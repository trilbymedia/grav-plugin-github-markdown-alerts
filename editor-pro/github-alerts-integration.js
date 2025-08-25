/**
 * GitHub Markdown Alerts Integration for Editor Pro
 * Adds toolbar button for inserting GitHub-style alerts using Editor Pro's modal system
 */

(function() {
    'use strict';

    // Wait for Editor Pro to be available
    function waitForEditorPro(callback) {
        if (window.EditorPro && window.EditorPro.registerPlugin) {
            callback();
        } else {
            setTimeout(() => waitForEditorPro(callback), 100);
        }
    }

    // Editor Pro GitHub Alerts Plugin
    const EditorProGitHubAlertsPlugin = {
        name: 'github-markdown-alerts',
        
        init(editorPro) {
            this.editorPro = editorPro;
            
            // Only add the button if it doesn't already exist
            if (!editorPro.toolbar.querySelector('[data-toolbar-item="githubAlert"]')) {
                this.addToolbarButton();
            }
        },

        addToolbarButton() {
            // Create toolbar button matching Editor Pro's style
            const button = document.createElement('button');
            button.type = 'button';
            button.setAttribute('data-tooltip', 'Markdown Alert');
            button.setAttribute('data-toolbar-item', 'githubAlert');
            
            // Use the same icon as Editor Pro
            button.innerHTML = this.editorPro.getIcon('githubAlert');
            
            // Find where to insert the button
            const toolbar = this.editorPro.toolbar;
            const leftSection = toolbar.querySelector('.toolbar-left');
            
            if (leftSection) {
                // Find the HTML block button to insert after
                const htmlButton = leftSection.querySelector('[data-toolbar-item="htmlBlock"]');
                const shortcodeButton = leftSection.querySelector('[data-toolbar-item="shortcodeBlock"]');
                const insertAfter = shortcodeButton || htmlButton;
                
                if (insertAfter) {
                    insertAfter.parentNode.insertBefore(button, insertAfter.nextSibling);
                } else {
                    // Fallback: find code block button
                    const codeBlock = leftSection.querySelector('[data-toolbar-item="codeBlock"]');
                    if (codeBlock && codeBlock.nextSibling) {
                        codeBlock.parentNode.insertBefore(button, codeBlock.nextSibling.nextSibling);
                    } else {
                        leftSection.appendChild(button);
                    }
                }
            }

            // Use Editor Pro's modal system
            button.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.editorPro.showGitHubAlertSelector();
            };
        }
    };

    // Initialize when Editor Pro is ready
    waitForEditorPro(() => {
        // Register the plugin
        if (window.EditorPro.registerPlugin) {
            window.EditorPro.registerPlugin(EditorProGitHubAlertsPlugin);
        }
        
        console.log('GitHub Markdown Alerts integration loaded for Editor Pro');
    });

})();