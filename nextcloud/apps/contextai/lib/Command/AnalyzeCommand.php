<?php
namespace OCA\Contextai\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use OCP\Files\IRootFolder;
use OCA\Contextai\Service\GeminiService;

class AnalyzeCommand extends Command {
    private $rootFolder;
    private $geminiService;

    public function __construct(IRootFolder $rootFolder, GeminiService $geminiService) {
        parent::__construct();
        $this->rootFolder = $rootFolder;
        $this->geminiService = $geminiService;
    }

    protected function configure() {
        $this
            ->setName('contextai:analyze')
            ->setDescription('Analyze a file (PDF or Text) and save summary')
            ->addArgument('user_id', InputArgument::REQUIRED)
            ->addArgument('path', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $userId = $input->getArgument('user_id');
        $path = $input->getArgument('path');
        
        $output->writeln("Reading $path...");
        
        try {
            $userFolder = $this->rootFolder->getUserFolder($userId);
            if (!$userFolder->nodeExists($path)) {
                 $output->writeln("❌ File not found.");
                 return 1;
            }

            $file = $userFolder->get($path);
            
            // 1. Get MIME Type (e.g., 'application/pdf')
            $mimeType = $file->getMimeType();
            $output->writeln("Type detected: " . $mimeType);

            // 2. Read Content (Binary or Text)
            $content = $file->getContent();

            // 3. Send to AI
            $output->writeln("Sending to Gemini...");
            $summary = $this->geminiService->summarize($content, $mimeType);
            
            $output->writeln("AI Summary Generated!");
            
            // 4. Save Summary
            $newPath = $path . "_summary.txt";
            if (!$userFolder->nodeExists($newPath)) {
                $userFolder->newFile($newPath);
            }
            $summaryFile = $userFolder->get($newPath);
            $summaryFile->putContent($summary);

            $output->writeln("Saved summary to: $newPath");

        } catch (\Exception $e) {
            $output->writeln("Error: " . $e->getMessage());
            return 1;
        }
        return 0;
    }
}
