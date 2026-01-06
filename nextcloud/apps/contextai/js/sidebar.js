(function() {
    console.log("------------------------------------------------");
    console.log("🔵 Context AI: Debug Script STARTING...");
    
    // 1. VISUAL TEST: Add a Red Box to prove JS is running
    const visualTest = document.createElement('div');
    visualTest.innerText = "JS ACTIVE";
    visualTest.style.position = "fixed";
    visualTest.style.bottom = "10px";
    visualTest.style.right = "10px";
    visualTest.style.background = "red";
    visualTest.style.color = "white";
    visualTest.style.padding = "5px 10px";
    visualTest.style.zIndex = "99999";
    visualTest.style.fontWeight = "bold";
    document.body.appendChild(visualTest);
    console.log("🔵 Context AI: Red Visual Test Box Added");

    // 2. REGISTRATION LOOP
    let attempts = 0;
    const registerInterval = setInterval(() => {
        attempts++;
        console.log(`🔵 Context AI: Loop attempt #${attempts}...`);

        // Check if Nextcloud Files API exists
        if (window.OCA && window.OCA.Files && window.OCA.Files.fileActions) {
            console.log("🟢 Context AI: Found OCA.Files.fileActions!");

            // Check if already registered
            if (window.OCA.Files.fileActions.actions.all && 
                window.OCA.Files.fileActions.actions.all['context_ai_analyze']) {
                console.log("🟡 Context AI: Action already exists. Stopping.");
                clearInterval(registerInterval);
                return;
            }

            // Register the action
            try {
                window.OCA.Files.fileActions.registerAction({
                    name: 'context_ai_analyze',
                    displayName: '✨ Analyze with AI',
                    mime: 'all',
                    permissions: OC.PERMISSION_READ,
                    icon: OC.imagePath('core', 'actions/info'),
                    actionHandler: function (fileName, context) {
                        const fileId = context.fileInfoModel.id;
                        alert(`Analyzing ID: ${fileId}`);
                        
                        // Backend Call
                        const url = OC.generateUrl(`/apps/contextai/analyze/${fileId}`);
                        fetch(url)
                            .then(res => res.json())
                            .then(data => alert("Result: " + data.message))
                            .catch(err => alert("Error: " + err));
                    }
                });
                console.log("✅ Context AI: SUCCESS! Action Registered.");
                visualTest.style.background = "green";
                visualTest.innerText = "JS LINKED";
                clearInterval(registerInterval);
            } catch (e) {
                console.error("🔴 Context AI: Registration Error:", e);
            }
        } else {
            console.log("⚪ Context AI: OCA.Files not ready yet...");
        }

        // Stop after 30 seconds (30 attempts) to prevent infinite loops
        if (attempts > 30) {
            console.error("🔴 Context AI: Timeout! Could not find Files App.");
            clearInterval(registerInterval);
        }
    }, 1000);
})();