{{--
    Livewire view: message-composer-themed.
    Dashboard-themed variant of the message composer (classes prefixed
    "tm-"). Uses a separate Alpine data factory (messageComposerUploadThemed)
    so the themed and non-themed composers can co-exist on the same page.
--}}
<div class="tm-composer" x-data="messageComposerUploadThemed(@js($conversationId))" x-init="init()">
    {{-- Quick replies dropdown with user + channel scoped entries. --}}
    @if($showQuickReplies && count($quickReplies) > 0)
        <div class="tm-composer-quick-replies">
            <div class="tm-quick-header">
                <span>
                    <i class="ri ri-chat-quote-line"></i>
                    {{ __('messaging::messages.quick_replies') }}
                </span>
                <button wire:click="toggleQuickReplies" type="button" class="tm-quick-close">
                    <i class="ri ri-close-line"></i>
                </button>
            </div>
            <div class="tm-quick-list">
                @foreach($quickReplies as $reply)
                    <button
                        wire:click="selectQuickReply({{ $reply->id }})"
                        type="button"
                        class="tm-quick-item"
                    >
                        {{ $reply->title }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <form wire:submit="send" class="tm-composer-form">
        {{-- File Preview --}}
        @if($attachmentPath)
            <div class="tm-composer-preview">
                <div class="tm-preview-content">
                    <div class="tm-preview-icon">
                        @if(str_starts_with($attachmentMimeType ?? '', 'image/'))
                            <i class="ri ri-image-line"></i>
                        @elseif(str_starts_with($attachmentMimeType ?? '', 'video/'))
                            <i class="ri ri-video-line"></i>
                        @elseif(str_starts_with($attachmentMimeType ?? '', 'audio/'))
                            <i class="ri ri-mic-line"></i>
                        @else
                            <i class="ri ri-file-3-line"></i>
                        @endif
                    </div>
                    <div class="tm-preview-info">
                        <span class="tm-preview-name">{{ $attachmentName }}</span>
                        <span class="tm-preview-size">{{ number_format(($attachmentSize ?? 0) / 1024, 1) }} KB</span>
                    </div>
                    @if(str_starts_with($attachmentMimeType ?? '', 'audio/'))
                        <audio controls class="tm-preview-audio">
                            <source src="{{ asset('storage/' . $attachmentPath) }}" type="{{ $attachmentMimeType }}">
                        </audio>
                    @endif
                </div>
                <button wire:click="clearAttachment" type="button" class="tm-preview-remove">
                    <i class="ri ri-close-line"></i>
                </button>
            </div>
        @endif

        {{-- Voice Recording UI --}}
        <div x-show="isRecording" x-cloak class="tm-composer-recording">
            <div class="tm-recording-indicator">
                <span class="tm-recording-dot"></span>
            </div>
            <span class="tm-recording-label">{{ __('messaging::messages.recording') }}</span>
            <span class="tm-recording-time" x-text="recordingTime"></span>
            <div class="tm-recording-actions">
                <button @click.prevent="cancelRecording()" type="button" class="tm-recording-cancel" title="{{ __('messaging::messages.cancel') }}">
                    <i class="ri ri-close-line"></i>
                </button>
                <button @click.prevent="stopRecording()" type="button" class="tm-recording-send" title="{{ __('messaging::messages.send') }}">
                    <i class="ri ri-send-plane-fill"></i>
                </button>
            </div>
        </div>

        {{-- Upload Progress --}}
        <div x-show="uploading" x-cloak class="tm-composer-uploading">
            <div class="tm-upload-spinner"></div>
            <span>{{ __('messaging::messages.uploading') }}...</span>
        </div>

        {{-- Upload Error --}}
        <div x-show="uploadError" x-cloak class="tm-composer-error">
            <i class="ri ri-error-warning-line"></i>
            <span x-text="uploadError"></span>
            <button type="button" @click="uploadError = null">
                <i class="ri ri-close-line"></i>
            </button>
        </div>

        {{-- Input Area --}}
        <div class="tm-composer-input-area" x-show="!isRecording">
            <div class="tm-composer-input-wrapper">
                <textarea
                    wire:model="content"
                    x-on:keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); $wire.send(); }"
                    x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px';"
                    rows="1"
                    class="tm-composer-textarea"
                    placeholder="{{ __('messaging::messages.type_message') }}"
                ></textarea>
                @error('content')
                    <span class="tm-composer-input-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="tm-composer-actions">
                {{-- Quick Replies --}}
                @if(count($quickReplies) > 0)
                    <button
                        wire:click="toggleQuickReplies"
                        type="button"
                        class="tm-composer-action-btn"
                        title="{{ __('messaging::messages.quick_replies') }}"
                    >
                        <i class="ri ri-chat-quote-line"></i>
                    </button>
                @endif

                {{-- Voice Recording --}}
                <button
                    type="button"
                    class="tm-composer-action-btn tm-composer-action-voice"
                    title="{{ __('messaging::messages.voice_note') }}"
                    @click.prevent="startRecording()"
                    x-bind:disabled="uploading || !canRecord"
                >
                    <i class="ri ri-mic-line"></i>
                </button>

                {{-- Attachment --}}
                <div class="tm-composer-attach">
                    <input
                        type="file"
                        x-ref="fileInput"
                        @change="uploadFile($event)"
                        class="tm-composer-file-input"
                        accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar"
                    >
                    <button
                        type="button"
                        class="tm-composer-action-btn"
                        title="{{ __('messaging::messages.attach_file') }}"
                        @click.prevent="$refs.fileInput.click()"
                        x-bind:disabled="uploading"
                    >
                        <i class="ri ri-attachment-2"></i>
                    </button>
                </div>

                {{-- Send Button --}}
                <button
                    type="submit"
                    class="tm-composer-send-btn"
                    x-bind:disabled="$wire.isSending || uploading"
                    title="{{ __('messaging::messages.send') }}"
                >
                    @if($isSending)
                        <span class="tm-composer-spinner"></span>
                    @else
                        <i class="ri ri-send-plane-fill"></i>
                    @endif
                </button>
            </div>
        </div>
    </form>
</div>

@script
<script>
    Alpine.data('messageComposerUploadThemed', (passedConversationId) => ({
        uploading: false,
        uploadError: null,
        conversationId: passedConversationId,
        isRecording: false,
        canRecord: false,
        mediaRecorder: null,
        audioChunks: [],
        recordingTime: '00:00',
        recordingInterval: null,
        recordingStartTime: null,
        stream: null,

        init() {
            this.canRecord = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia && typeof MediaRecorder !== 'undefined');
        },

        async startRecording() {
            if (!this.canRecord) {
                this.uploadError = 'Voice recording is not supported in this browser';
                return;
            }

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ audio: true });

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

                this.mediaRecorder = new MediaRecorder(this.stream, { mimeType });
                this.audioChunks = [];

                const self = this;

                this.mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        self.audioChunks.push(event.data);
                    }
                };

                this.mediaRecorder.onstop = async () => {
                    if (self.stream) {
                        self.stream.getTracks().forEach(track => track.stop());
                    }

                    if (self.audioChunks.length > 0) {
                        const audioBlob = new Blob(self.audioChunks, { type: mimeType });
                        await self.uploadVoiceNote(audioBlob, mimeType);
                    }
                };

                this.mediaRecorder.start(100);
                this.isRecording = true;
                this.recordingStartTime = Date.now();
                this.startTimer();

            } catch (error) {
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

                if (elapsed >= 300) {
                    self.stopRecording();
                }
            }, 1000);
        },

        stopRecording() {
            if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                this.mediaRecorder.stop();
            }
            this.clearRecordingState();
        },

        cancelRecording() {
            this.audioChunks = [];

            if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                this.mediaRecorder.stop();
            }

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
                    await this.$wire.setAttachmentAndSend({
                        path: data.path,
                        name: data.name,
                        mime_type: data.mime_type,
                        size: data.size
                    });
                } else {
                    this.uploadError = data.message || 'Upload failed';
                }
            } catch (error) {
                this.uploadError = 'Upload failed. Please try again.';
            } finally {
                this.uploading = false;
            }
        },

        async uploadFile(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.uploadError = null;

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
                this.uploadError = 'Upload failed. Please try again.';
            } finally {
                this.uploading = false;
                event.target.value = '';
            }
        }
    }));
</script>
@endscript
