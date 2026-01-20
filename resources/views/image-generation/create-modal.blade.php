<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Generate Image</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white">
    <div class="p-6 bg-white">
        <form id="imageGenerationForm" class="space-y-6">
            @csrf

            <!-- Prompt -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="prompt" class="block text-sm font-medium text-gray-700">
                        Image Prompt *
                    </label>
                    <button type="button" id="generatePromptBtn"
                            onclick="generatePromptFromContent()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-full transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Generate Prompt
                    </button>
                </div>
                <textarea
                    name="prompt"
                    id="prompt"
                    rows="4"
                    required
                    maxlength="3000"
                    placeholder="Describe the image you want to generate..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                <p class="text-xs text-gray-500 mt-1"><span id="promptCounter">0</span>/3000 characters</p>
            </div>

            <!-- AI Provider -->
            <div>
                <label for="provider" class="block text-sm font-medium text-gray-700 mb-2">
                    AI Provider
                </label>
                <select
                    name="provider"
                    id="provider"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Auto (use default)</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider['value'] }}">{{ $provider['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Image Style -->
            <div>
                <label for="image_style" class="block text-sm font-medium text-gray-700 mb-2">
                    Image Style *
                </label>
                <select
                    name="image_style"
                    id="image_style"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="Photo">Photo</option>
                    <option value="Cartoon">Cartoon</option>
                    <option value="Cubism">Cubism</option>
                    <option value="Expressionism">Expressionism</option>
                    <option value="Cyberpunk">Cyberpunk</option>
                    <option value="Fantasy">Fantasy</option>
                    <option value="Cinematic">Cinematic</option>
                    <option value="Abstract">Abstract</option>
                    <option value="Impressionism">Impressionism</option>
                    <option value="Surrealism">Surrealism</option>
                    <option value="Anime">Anime</option>
                    <option value="Comic">Comic</option>
                </select>
            </div>

            <!-- Image Size -->
            <div>
                <label for="image_size" class="block text-sm font-medium text-gray-700 mb-2">
                    Image Size *
                </label>
                <select
                    name="image_size"
                    id="image_size"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="1080x1350 (4:5 Instagram Portrait)">1080x1350 (4:5 Instagram Portrait)</option>
                    <option value="960x768 (5:4)">960x768 (5:4)</option>
                    <option value="1024x640 (8:5)">1024x640 (8:5)</option>
                    <option value="1024x768 (4:3)">1024x768 (4:3)</option>
                    <option value="1152x768 (3:2)">1152x768 (3:2)</option>
                    <option value="768x1344 (9:16)">768x1344 (9:16)</option>
                    <option value="1024x1024 (1:1)">1024x1024 (1:1)</option>
                </select>
            </div>

            <!-- Context Title (Hidden) - For language detection -->
            <input type="hidden" name="context_title" id="context_title" value="">

            <!-- Progress indicator (hidden initially) -->
            <div id="progressContainer" class="hidden">
                <div class="bg-gray-100 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Generating image...</p>
                            <p class="text-xs text-gray-500">This may take 30-60 seconds</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="window.parent.postMessage({type: 'close-modal'}, '*')"
                        class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit"
                        id="submitButton"
                        class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg transition shadow-lg">
                    Generate Image
                </button>
            </div>
        </form>
    </div>

    <script>
    // Get post title from parent window for language detection
    try {
        const postTitle = window.parent.document.querySelector('input[name="title"]')?.value || '';
        if (postTitle) {
            document.getElementById('context_title').value = postTitle;
        }
    } catch (e) {
        console.log('Cannot access parent title (might be standalone mode)');
    }

    // Prompt character counter
    const promptTextarea = document.getElementById('prompt');
    const promptCounter = document.getElementById('promptCounter');

    promptTextarea.addEventListener('input', function() {
        promptCounter.textContent = this.value.length;
    });

    // Form submission
    const form = document.getElementById('imageGenerationForm');
    const submitButton = document.getElementById('submitButton');
    const progressContainer = document.getElementById('progressContainer');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        // Show progress, disable button
        submitButton.disabled = true;
        submitButton.textContent = 'Generating...';
        progressContainer.classList.remove('hidden');

        // First store in session
        fetch('{{ route("image-generation.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(res => {
            if (!res.ok) throw new Error('Failed to start generation');
            return fetch('{{ route("image-generation.run") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                // Reset form
                form.reset();
                document.getElementById('prompt').value = '';
                promptCounter.textContent = '0';

                // Reset button states
                submitButton.disabled = false;
                submitButton.textContent = 'Generate Image';
                progressContainer.classList.add('hidden');

                // Notify parent and close modal
                window.parent.postMessage({
                    type: 'image-generated',
                    media: result
                }, '*');
            } else {
                throw new Error(result.error || 'Generation failed');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
            submitButton.disabled = false;
            submitButton.textContent = 'Generate Image';
            progressContainer.classList.add('hidden');
        });
    });

    // Generate Prompt from Content
    function generatePromptFromContent() {
        const btn = document.getElementById('generatePromptBtn');
        const originalText = btn.innerHTML;

        // Get content from parent window (post content)
        let articleContent = '';
        try {
            articleContent = window.parent.document.querySelector('[name="content"]')?.value || '';
        } catch (e) {
            console.log('Cannot access parent content');
        }

        // If no content, ask user what they want
        if (!articleContent.trim()) {
            const userInput = prompt('Artikel belum ada isinya. Kamu mau bikin gambar apa?');

            if (!userInput) {
                return; // User cancelled
            }

            articleContent = userInput;
        }

        // Disable button and show loading
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Generating...';

        // Call API to generate prompt
        fetch('{{ route("image-generation.generate-prompt") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                content: articleContent
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Set generated prompt to textarea
                document.getElementById('prompt').value = data.prompt;
                promptCounter.textContent = data.prompt.length;

                // Show success feedback
                btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Generated!';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 2000);
            } else if (data.needs_input) {
                // Need user input
                const userInput = prompt(data.message);
                if (userInput) {
                    // Retry with user input
                    fetch('{{ route("image-generation.generate-prompt") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            user_input: userInput
                        })
                    })
                    .then(res => res.json())
                    .then(retryData => {
                        if (retryData.success) {
                            document.getElementById('prompt').value = retryData.prompt;
                            promptCounter.textContent = retryData.prompt.length;
                        }
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    });
                } else {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } else {
                throw new Error(data.error || 'Failed to generate prompt');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
    </script>
</body>
</html>
