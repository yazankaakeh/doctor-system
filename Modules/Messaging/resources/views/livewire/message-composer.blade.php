<div class="border-top p-3 bg-white" x-data="messageComposerUpload(@js($conversationId))" x-init="init()">
    <style>
        [x-cloak] { display: none !important; }
        .pulse-animation {
            animation: pulse-recording 1.5s infinite;
        }
        @keyframes pulse-recording {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
            100% { opacity: 1; transform: scale(1); }
        }
    </style>

    {{-- Quick Replies --}}
    @if($showQuickReplies && count($quickReplies) > 0)
        <div class="mb-3 p-3 bg-light rounded-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fs-7 fw-semibold text-gray-700">
                    <i class="ki-outline ki-message-text fs-6 me-1"></i>
                    {{ __('messaging::messages.quick_replies') }}
                </span>
                <button wire:click="toggleQuickReplies" type="button" class="btn btn-sm btn-icon btn-light-danger">
                    <i class="ki-outline ki-cross fs-6"></i>
                </button>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($quickReplies as $reply)
                    <button
                        wire:click="selectQuickReply({{ $reply->id }})"
                        type="button"
                        class="btn btn-sm btn-light-primary"
                    >
                        {{ $reply->title }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <form wire:submit="send">
        {{-- File Preview --}}
        @if($attachmentPath)
            <div class="mb-3 p-3 bg-light-primary rounded-3 border border-primary border-dashed">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="symbol symbol-45px">
                            <span class="symbol-label bg-light-primary rounded">
                                @if(str_starts_with($attachmentMimeType ?? '', 'image/'))
                                    <i class="ki-outline ki-picture fs-2 text-primary"></i>
                                @elseif(str_starts_with($attachmentMimeType ?? '', 'video/'))
                                    <i class="ki-outline ki-faceid fs-2 text-primary"></i>
                                @elseif(str_starts_with($attachmentMimeType ?? '', 'audio/'))
                                    <i class="ki-outline ki-microphone fs-2 text-primary"></i>
                                @else
                                    <i class="ki-outline ki-file fs-2 text-primary"></i>
                                @endif
                            </span>
                        </div>

                        <div class="d-flex flex-column">
                            <span class="fs-7 fw-semibold text-gray-800 text-truncate" style="max-width: 200px;">
                                {{ $attachmentName }}
                            </span>
                            <span class="fs-8 text-gray-500">
                                {{ number_format(($attachmentSize ?? 0) / 1024, 1) }} KB
                            </span>
                        </div>

                        {{-- Audio Preview Player --}}
                        @if(str_starts_with($attachmentMimeType ?? '', 'audio/'))
                            <audio controls class="ms-2" style="height: 32px; max-width: 150px;">
                                <source src="{{ asset('storage/' . $attachmentPath) }}" type="{{ $attachmentMimeType }}">
                            </audio>
                        @endif
                    </div>

                    <button wire:click="clearAttachment" type="button" class="btn btn-sm btn-icon btn-light-danger">
                        <i class="ki-outline ki-cross fs-5"></i>
                    </button>
                </div>
            </div>
        @endif

        {{-- Voice Recording UI --}}
        <div x-show="isRecording" x-cloak class="mb-3">
            <div class="d-flex align-items-center justify-content-between p-3 bg-light-danger rounded-3 border border-danger border-dashed">
                <div class="d-flex align-items-center gap-3">
                    <div class="recording-indicator">
                        <span class="bg-danger rounded-circle pulse-animation d-inline-block" style="width: 12px; height: 12px;"></span>
                    </div>
                    <span class="fs-7 fw-semibold text-danger">{{ __('messaging::messages.recording') }}</span>
                    <span class="fs-7 text-gray-600 font-monospace" x-text="recordingTime"></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button @click.prevent="cancelRecording()" type="button" class="btn btn-sm btn-icon btn-light-danger" title="{{ __('messaging::messages.cancel') }}">
                        <i class="ki-outline ki-cross fs-5"></i>
                    </button>
                    <button @click.prevent="stopRecording()" type="button" class="btn btn-sm btn-icon btn-danger" title="{{ __('messaging::messages.send') }}">
                        <i class="ki-outline ki-send fs-5"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Upload Progress --}}
        <div x-show="uploading" x-cloak class="mb-3">
            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="fs-7 text-gray-600">{{ __('messaging::messages.uploading') }}...</span>
            </div>
        </div>

        {{-- Upload Error --}}
        <div x-show="uploadError" x-cloak class="mb-3">
            <div class="alert alert-danger py-2 px-3 fs-7 mb-0">
                <i class="ki-outline ki-information fs-6 me-1"></i>
                <span x-text="uploadError"></span>
                <button type="button" class="btn-close btn-sm float-end" @click="uploadError = null"></button>
            </div>
        </div>

        {{-- Input Area - Always visible, hide buttons when recording --}}
        <div class="d-flex align-items-end gap-2">
            <div class="flex-grow-1" x-show="!isRecording">
                <textarea
                    wire:model="content"
                    rows="1"
                    class="form-control form-control-solid border-0"
                    style="resize: none; min-height: 40px; max-height: 120px;"
                    placeholder="{{ __('messaging::messages.type_message') }}"
                    onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); @this.send(); }"
                    oninput="this.style.height = 'auto'; this.style.height = Math.min(this.scrollHeight, 120) + 'px';"
                ></textarea>
                @error('content') <span class="text-danger fs-8 mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="d-flex align-items-center gap-1" x-show="!isRecording">
                {{-- Quick Replies Button --}}
                @if(count($quickReplies) > 0)
                    <button
                        wire:click="toggleQuickReplies"
                        type="button"
                        class="btn btn-sm btn-icon btn-light-primary"
                        title="{{ __('messaging::messages.quick_replies') }}"
                    >
                        <i class="ki-outline ki-message-text fs-4"></i>
                    </button>
                @endif

                {{-- Voice Recording Button --}}
                <button
                    type="button"
                    class="btn btn-sm btn-icon btn-light-danger"
                    title="{{ __('messaging::messages.voice_note') }}"
                    @click.prevent="startRecording()"
                    x-bind:disabled="uploading || !canRecord"
                >
                    <i class="ki-outline ki-microphone fs-4"></i>
                </button>

                {{-- Attachment Button --}}
                <div class="position-relative">
                    <input
                        type="file"
                        x-ref="fileInput"
                        @change="uploadFile($event)"
                        class="d-none"
                        accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar"
                    >
                    <button
                        type="button"
                        class="btn btn-sm btn-icon btn-light-info"
                        title="{{ __('messaging::messages.attach_file') }}"
                        @click.prevent="$refs.fileInput.click()"
                        x-bind:disabled="uploading"
                    >
                        <i class="ki-outline ki-paper-clip fs-4"></i>
                    </button>
                </div>

                {{-- Send Button --}}
                <button
                    type="submit"
                    class="btn btn-sm btn-icon btn-primary"
                    x-bind:disabled="$wire.isSending || uploading"
                    title="{{ __('messaging::messages.send') }}"
                >
                    @if($isSending)
                        <span class="spinner-border spinner-border-sm"></span>
                    @else
                        <i class="ki-outline ki-send fs-4"></i>
                    @endif
                </button>
            </div>
        </div>
    </form>
</div>

@script
<script>
    Alpine.data('messageComposerUpload', (passedConversationId) => ({
        uploading: false,
        uploadError: null,
        conversationId: passedConversationId,

        // Voice recording
        isRecording: false,
        canRecord: false,
        mediaRecorder: null,
        audioChunks: [],
        recordingTime: '00:00',
        recordingInterval: null,
        recordingStartTime: null,
        stream: null,

        init() {
            // Check if browser supports recording
            this.canRecord = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia && typeof MediaRecorder !== 'undefined');
            console.log('Voice recording supported:', this.canRecord);
            console.log('MessageComposer init - conversationId passed to Alpine:', this.conversationId);
            console.log('MessageComposer init - $wire.conversationId:', this.$wire?.conversationId);

            // Test Livewire call
            this.testLivewireCall();
        },

        async testLivewireCall() {
            try {
                console.log('Testing Livewire call...');
                const result = await this.$wire.testCall();
                console.log('Test call result:', result);
            } catch (error) {
                console.error('Test call failed:', error);
            }
        },

        async startRecording() {
            console.log('Starting recording...');

            if (!this.canRecord) {
                this.uploadError = 'Voice recording is not supported in this browser';
                return;
            }

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                console.log('Got audio stream');

                // Determine the best supported format
                let mimeType = 'audio/webm';
                if (MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) {
                    mimeType = 'audio/webm;codecs=opus';
                } else if (MediaRecorder.isTypeSupported('audio/webm')) {
                    mimeType = 'audio/webm';
                } else if (MediaRecorder.isTypeSupported('audio/ogg;codecs=opus')) {
                    mimeType = 'audio/ogg;codecs=opus';
                } else if (MediaRecorder.isTypeSupported('audio/mp4')) {
                    mimeType = 'audio/mp4';
                }
                console.log('Using mime type:', mimeType);

                this.mediaRecorder = new MediaRecorder(this.stream, { mimeType });
                this.audioChunks = [];

                const self = this;

                this.mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        self.audioChunks.push(event.data);
                    }
                };

                this.mediaRecorder.onstop = async () => {
                    console.log('Recording stopped, chunks:', self.audioChunks.length);

                    // Stop all tracks
                    if (self.stream) {
                        self.stream.getTracks().forEach(track => track.stop());
                    }

                    if (self.audioChunks.length > 0) {
                        const audioBlob = new Blob(self.audioChunks, { type: mimeType });
                        console.log('Created blob, size:', audioBlob.size);
                        await self.uploadVoiceNote(audioBlob, mimeType);
                    }
                };

                this.mediaRecorder.start(100);
                this.isRecording = true;
                this.recordingStartTime = Date.now();
                this.startTimer();
                console.log('Recording started');

            } catch (error) {
                console.error('Error starting recording:', error);
                if (error.name === 'NotAllowedError') {
                    this.uploadError = 'Microphone access denied. Please allow microphone access.';
                } else {
                    this.uploadError = 'Could not start recording: ' + error.message;
                }
            }
        },

        startTimer() {
            const self = this;
            this.recordingInterval = setInterval(() => {
                const elapsed = Math.floor((Date.now() - self.recordingStartTime) / 1000);
                const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
                const seconds = (elapsed % 60).toString().padStart(2, '0');
                self.recordingTime = `${minutes}:${seconds}`;

                // Auto-stop after 5 minutes
                if (elapsed >= 300) {
                    self.stopRecording();
                }
            }, 1000);
        },

        stopRecording() {
            console.log('Stopping recording...');
            if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                this.mediaRecorder.stop();
            }
            this.clearRecordingState();
        },

        cancelRecording() {
            console.log('Canceling recording...');
            // Clear chunks before stopping so nothing gets uploaded
            this.audioChunks = [];

            if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                this.mediaRecorder.stop();
            }

            // Stop all tracks
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
            }

            this.clearRecordingState();
        },

        clearRecordingState() {
            this.isRecording = false;
            this.recordingTime = '00:00';
            if (this.recordingInterval) {
                clearInterval(this.recordingInterval);
                this.recordingInterval = null;
            }
        },

        async uploadVoiceNote(audioBlob, mimeType) {
            this.uploading = true;
            this.uploadError = null;

            try {
                // Determine file extension
                let extension = 'webm';
                if (mimeType.includes('ogg')) extension = 'ogg';
                else if (mimeType.includes('mp4')) extension = 'm4a';
                else if (mimeType.includes('mp3')) extension = 'mp3';

                const fileName = `voice-note-${Date.now()}.${extension}`;
                const file = new File([audioBlob], fileName, { type: mimeType });

                const formData = new FormData();
                formData.append('file', file);

                const response = await fetch('/messaging/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    console.log('Upload success, sending voice note...');
                    console.log('Data to send:', data);
                    console.log('Alpine conversationId:', this.conversationId);
                    console.log('$wire object:', this.$wire);
                    console.log('$wire.conversationId:', this.$wire?.conversationId);
                    console.log('$wire.attachmentPath:', this.$wire?.attachmentPath);
                    console.log('setAttachmentAndSend method:', typeof this.$wire?.setAttachmentAndSend);

                    if (!this.$wire) {
                        console.error('$wire is not available!');
                        this.uploadError = '$wire not available - Livewire component not found';
                        return;
                    }

                    try {
                        console.log('Calling setAttachmentAndSend now...');
                        // Set attachment and send in one call
                        const result = await this.$wire.setAttachmentAndSend({
                            path: data.path,
                            name: data.name,
                            mime_type: data.mime_type,
                            size: data.size
                        });
                        console.log('Voice note sent, result:', result);
                    } catch (error) {
                        console.error('Error calling setAttachmentAndSend:', error);
                        console.error('Error stack:', error.stack);
                        this.uploadError = 'Failed to send voice note: ' + error.message;
                    }
                } else {
                    this.uploadError = data.message || 'Upload failed';
                    console.error('Upload failed:', data.message);
                }
            } catch (error) {
                console.error('Upload error:', error);
                this.uploadError = 'Upload failed. Please try again.';
            } finally {
                this.uploading = false;
            }
        },

        async uploadFile(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Reset error
            this.uploadError = null;

            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                this.uploadError = 'File size must be less than 10MB';
                event.target.value = '';
                return;
            }

            this.uploading = true;

            try {
                const formData = new FormData();
                formData.append('file', file);

                const response = await fetch('/messaging/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.$wire.setAttachment({
                        path: data.path,
                        name: data.name,
                        mime_type: data.mime_type,
                        size: data.size
                    });
                } else {
                    this.uploadError = data.message || 'Upload failed';
                }
            } catch (error) {
                console.error('Upload error:', error);
                this.uploadError = 'Upload failed. Please try again.';
            } finally {
                this.uploading = false;
                event.target.value = '';
            }
        }
    }));
</script>
@endscript
