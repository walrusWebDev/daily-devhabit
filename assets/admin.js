/**
 * Daily Dev Habit Admin JS
 * Version: 0.3.0
 */

jQuery(document).ready(function($) {
    
    // ==========================================
    // 1. Settings Page Toggle Logic
    // ==========================================
    const modeSelect = $('#ddh_connection_mode');
    
    function toggleSettingsFields() {
        const mode = modeSelect.val();
        
        // Use closest('tr') to hide the entire table row in WP Admin
        if (mode === 'cloud') {
            $('.ddh-github-field').closest('tr').hide();
            $('.ddh-cloud-field').closest('tr').show();
        } else {
            $('.ddh-github-field').closest('tr').show();
            $('.ddh-cloud-field').closest('tr').hide();
        }
    }

    if (modeSelect.length > 0) {
        // Run on load
        toggleSettingsFields();
        // Run on change
        modeSelect.on('change', toggleSettingsFields);
    }
});


document.addEventListener('DOMContentLoaded', () => {

    const appContainer = document.getElementById('appContainer');
    if (!appContainer) {
        // We might be on the settings page, not the app page. Exit gracefully.
        return; 
    }
    
    // Check if localized data is available using the NEW handle
    if (typeof ddh_ajax === 'undefined') {
        console.error('Daily Dev Habit: Localized AJAX data (ddh_ajax) not found.');
        appContainer.innerHTML = '<div class="notice notice-error"><p>Error: Plugin configuration data is missing.</p></div>';
        return;
    }

    // --- Configuration ---
    let questions = [];

    // Check if PHP sent us custom questions
    if (ddh_ajax.questions && ddh_ajax.questions.length > 0) {
        questions = ddh_ajax.questions;
    } else {
        // Fallback Defaults
        questions = [
            { prompt: "How many hours did you code?", placeholder: "e.g., 5 hours." },
            { prompt: "What was the main theme or project you focused on today?", placeholder: "e.g., Express server refactoring." },
            { prompt: "What was the single biggest task you completed?", placeholder: "e.g., Implemented JWT authentication." },
            { prompt: "Describe a significant challenge or problem you encountered.", placeholder: "e.g., Docker networking issues between containers." },
            { prompt: "How did you approach solving it? What was your thought process?", placeholder: "e.g., Used docker logs to identify the crash, then fixed the connection string." },
            { prompt: "What's something new you learned today? (A tool, a technique, a concept)", placeholder: "e.g., The 'Strategy Pattern' in PHP." },
            { prompt: "What's one thing you're proud of from today's work?", placeholder: "e.g., Successfully connecting WordPress to Node.js." },
            { prompt: "Based on today, what is the most important priority for tomorrow?", placeholder: "e.g., Building the Frontend Dashboard." }
        ];
    }
    

    // --- State Management ---
    let currentQuestionIndex = 0;
    let answers = new Array(questions.length).fill('');
    let isSaving = false; 

    // --- App Structure ---
    function renderApp() {
        const today = new Date();
        const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
        const formattedDate = today.toLocaleDateString(undefined, dateOptions);
        const saveButtonText = (ddh_ajax.mode === 'cloud') ? 'Save to Cloud' : 'Save to GitHub';

        appContainer.innerHTML = `
            <div class="card">
                <p class="subtitle header">Follow the prompts to create a log for <strong style="color: #1e293b;">${formattedDate}</strong>.</p>

                <div id="questionContainer" class="fade-in">
                    <div class="question-wrapper">
                        <label id="questionLabel" for="answerInput"></label>
                        <textarea id="answerInput" rows="5"></textarea>
                    </div>
                    <div class="navigation">
                        <button id="prevBtn" class="btn btn-secondary">Previous</button>
                        <div id="progressIndicator"></div>
                        <button id="nextBtn" class="btn btn-primary">Next</button>
                    </div>
                </div>

                <div id="resultContainerWrapper" class="hidden fade-in">
                    <h2 class="result-title">Your Daily Log</h2>
                    <div id="resultContainer" class="result-box"></div>
                    <div class="result-actions">
                        <button id="copyBtn" class="btn btn-secondary">Copy to Clipboard</button>                        
                        <button id="saveCloudBtn" class="btn btn-primary">${saveButtonText}</button>                         
                        <button id="restartBtn" class="btn btn-secondary">Start Over</button>
                    </div>
                    <div id="copySuccessMessage" class="copy-success hidden">Copied successfully!</div>
                    <div id="saveStatusMessage" class="github-status hidden"></div>
                </div>
            </div>
        `;
        addEventListeners();
        showQuestion();
    }

    // --- DOM Element References & Event Listeners ---
    let questionContainer, questionLabel, answerInput, prevBtn, nextBtn, progressIndicator,
        resultContainerWrapper, resultContainer, copyBtn, restartBtn, copySuccessMessage,
        saveCloudBtn, saveStatusMessage;

    function addEventListeners() {
        questionContainer = document.getElementById('questionContainer');
        questionLabel = document.getElementById('questionLabel');
        answerInput = document.getElementById('answerInput');
        prevBtn = document.getElementById('prevBtn');
        nextBtn = document.getElementById('nextBtn');
        progressIndicator = document.getElementById('progressIndicator');
        resultContainerWrapper = document.getElementById('resultContainerWrapper');
        resultContainer = document.getElementById('resultContainer');
        copyBtn = document.getElementById('copyBtn');
        restartBtn = document.getElementById('restartBtn');
        copySuccessMessage = document.getElementById('copySuccessMessage');
        
        saveCloudBtn = document.getElementById('saveCloudBtn');
        saveStatusMessage = document.getElementById('saveStatusMessage');

        nextBtn.addEventListener('click', handleNext);
        prevBtn.addEventListener('click', handlePrev);
        copyBtn.addEventListener('click', copyToClipboard);
        restartBtn.addEventListener('click', handleRestart);
        saveCloudBtn.addEventListener('click', saveLog); // Generic name
        answerInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && (e.metaKey || e.ctrlKey)) {
                e.preventDefault();
                handleNext();
            }
        });
    }
    
    function showQuestion() {
        if (!questionContainer) return; 
        questionContainer.classList.remove('fade-in');
        void questionContainer.offsetWidth;
        questionContainer.classList.add('fade-in');

        const currentQuestion = questions[currentQuestionIndex];
        if (questionLabel) questionLabel.textContent = currentQuestion.prompt;
        if (answerInput) {
            answerInput.placeholder = currentQuestion.placeholder;
            answerInput.value = answers[currentQuestionIndex];
            answerInput.focus();
        }
        updateProgress();
        updateButtonStates();
    }

    function updateProgress() {
        if (progressIndicator) {
             progressIndicator.textContent = `Step ${currentQuestionIndex + 1} of ${questions.length}`;
        }
    }

    function updateButtonStates() {
        if (prevBtn) prevBtn.disabled = currentQuestionIndex === 0;
        if (nextBtn) {
            nextBtn.textContent = (currentQuestionIndex === questions.length - 1) ? 'Finish' : 'Next';
        }
    }

    function handleNext() {
        saveAnswer();
        if (currentQuestionIndex < questions.length - 1) {
            currentQuestionIndex++;
            showQuestion();
        } else {
            showResult();
        }
    }

    function handlePrev() {
        saveAnswer();
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            showQuestion();
        }
    }

    function saveAnswer() {
        if (answerInput) {
             answers[currentQuestionIndex] = answerInput.value.trim();
        }
    }

    function showResult() {
        if (questionContainer) questionContainer.classList.add('hidden');
        if (resultContainerWrapper) resultContainerWrapper.classList.remove('hidden');

        let markdownOutput = `## Daily Log: ${new Date().toLocaleDateString()}\n\n`;
        questions.forEach((q, index) => {
            if (answers[index]) {
                markdownOutput += `### ${q.prompt}\n`;
                markdownOutput += `${answers[index]}\n\n`;
            }
        });
        if (resultContainer) resultContainer.textContent = markdownOutput.trim(); 
        
        hideStatus();
    }

    function copyToClipboard() {
        if (!resultContainer || !resultContainer.textContent) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(resultContainer.textContent).then(() => {
                showCopySuccess();
            }).catch(err => {
                fallbackCopyTextToClipboard(resultContainer.textContent); 
            });
        } else {
            fallbackCopyTextToClipboard(resultContainer.textContent);
        }
    }

    function fallbackCopyTextToClipboard(text) {
        const tempTextArea = document.createElement('textarea');
        tempTextArea.value = text;
        tempTextArea.style.position = 'fixed'; 
        document.body.appendChild(tempTextArea);
        tempTextArea.focus();
        tempTextArea.select();
        try {
            document.execCommand('copy');
            showCopySuccess();
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
        }
        document.body.removeChild(tempTextArea);
    }


    function showCopySuccess() {
        if (copySuccessMessage) {
            copySuccessMessage.classList.remove('hidden');
            setTimeout(() => copySuccessMessage.classList.add('hidden'), 2000);
        }
        hideStatus();
    }

    function handleRestart() {
        currentQuestionIndex = 0;
        answers.fill('');
        if (resultContainerWrapper) resultContainerWrapper.classList.add('hidden');
        if (questionContainer) questionContainer.classList.remove('hidden');
        hideStatus();
        showQuestion();
    }
    
    function saveLog() {
        if (isSaving || !resultContainer || !resultContainer.textContent) {
            return; 
        }
        isSaving = true;
        showStatus('Saving log...', 'loading');
        disableResultButtons(true);

        const logContent = resultContainer.textContent;

        const formData = new FormData();
        formData.append('action', 'ddh_save_log');         
        formData.append('nonce', ddh_ajax.nonce);            
        formData.append('log_content', logContent);       

        fetch(ddh_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.data || `HTTP error! Status: ${response.status}`);
                }).catch(() => {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                let successMsg = data.data.message || 'Log saved successfully!';
                showStatus(successMsg, 'success');
            } else {
                showStatus(`Error: ${data.data || 'Unknown error.'}`, 'error');
            }
        })
        .catch(error => {
            console.error('Error saving log:', error);
            showStatus(`Error: ${error.message || 'Failed to send request.'}`, 'error');
        })
        .finally(() => {
            isSaving = false;
            disableResultButtons(false);
        });
    }

    function showStatus(message, type = 'loading') { 
        if (saveStatusMessage) {
            saveStatusMessage.innerHTML = message; 
            saveStatusMessage.className = 'github-status'; 
            saveStatusMessage.classList.add(`status-${type}`);
            saveStatusMessage.classList.remove('hidden');
        }
         if(copySuccessMessage) copySuccessMessage.classList.add('hidden');
    }

    function hideStatus() {
        if (saveStatusMessage) {
            saveStatusMessage.classList.add('hidden');
            saveStatusMessage.textContent = '';
            saveStatusMessage.className = 'github-status hidden'; 
        }
    }

    function disableResultButtons(disabled) {
         if (copyBtn) copyBtn.disabled = disabled;
         if (saveCloudBtn) saveCloudBtn.disabled = disabled;
         if (restartBtn) restartBtn.disabled = disabled;
    }

    // --- Initial Load ---
    renderApp();
});