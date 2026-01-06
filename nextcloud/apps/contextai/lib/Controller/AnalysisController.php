<?php
namespace OCA\Contextai\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\Files\IRootFolder;
use OCA\Contextai\Service\GeminiService;

class AnalysisController extends Controller {
    private $rootFolder;
    private $geminiService;
    private $userId;

    public function __construct(string $AppName, IRequest $request, IRootFolder $rootFolder, GeminiService $geminiService, $UserId) {
        parent::__construct($AppName, $request);
        $this->rootFolder = $rootFolder;
        $this->geminiService = $geminiService;
        $this->userId = $UserId;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function analyze(int $fileId) {
        try {
            // 1. Find the file for the current user
            $userFolder = $this->rootFolder->getUserFolder($this->userId);
            $files = $userFolder->getById($fileId);

            if (empty($files)) {
                return new DataResponse(['error' => 'File not found'], 404);
            }
            $file = $files[0];

            // 2. Process content
            $content = $file->getContent();
            $mime = $file->getMimeType();

            // 3. Call AI Service
            $summary = $this->geminiService->summarize($content, $mime);

            // 4. Save Summary to File (Auto-documentation)
            $summaryPath = $file->getPath() . "_summary.txt";
            // Check if summary file already exists to avoid overwrite or errors (optional logic)
            // For now, we just overwrite or create new
             if (!$this->rootFolder->nodeExists($summaryPath)) {
                 $this->rootFolder->newFile($summaryPath);
             }
             $sFile = $this->rootFolder->get($summaryPath);
             $sFile->putContent($summary);

            // 5. Return JSON for the API user
            return new DataResponse([
                'status' => 'success',
                'file' => $file->getName(),
                'summary' => $summary,
                'saved_to' => $summaryPath
            ]);

        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }
}
