<div x-data="{
    message: '',
    copied: false,
    copiedImg: false,
    copiedBlob: false,
    textarea: null,
    init() {
        this.$nextTick(() => {
            this.bindTextarea();
        });
        setInterval(() => {
            this.bindTextarea();
            if (this.textarea && this.textarea.value) {
                this.message = this.textarea.value;
            }
        }, 250);
    },
    bindTextarea() {
        let el = $el.closest('form')?.querySelector('textarea') 
            || document.querySelector('form textarea')
            || document.querySelector('textarea');

        if (el && el !== this.textarea) {
            this.textarea = el;
            this.message = el.value || '';

            const events = ['input', 'keyup', 'change', 'paste', 'blur'];
            events.forEach(evt => {
                el.addEventListener(evt, () => {
                    this.message = el.value;
                });
            });
        } else if (!this.textarea && typeof $wire !== 'undefined') {
            this.message = $wire.get('mountedTableActionData.message') 
                || $wire.get('mountedActionData.message') 
                || $wire.get('data.message') 
                || '';
        }
    },
    copyText() {
        let textToCopy = this.textarea ? this.textarea.value : this.message;

        if (!textToCopy && typeof $wire !== 'undefined') {
            textToCopy = $wire.get('mountedTableActionData.message') 
                || $wire.get('mountedActionData.message') 
                || $wire.get('data.message') 
                || '';
        }

        if (textToCopy) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                }).catch(() => {
                    this.fallbackCopy(textToCopy);
                });
            } else {
                this.fallbackCopy(textToCopy);
            }
        }
    },
    async copyImage(url) {
        try {
            const proxyUrl = '/image-proxy?url=' + encodeURIComponent(url);
            const pngBlob = await this.imageUrlToPngBlob(proxyUrl);

            if (!pngBlob) {
                throw new Error('Canvas PNG generation failed');
            }

            const item = new ClipboardItem({ 'image/png': pngBlob });
            await navigator.clipboard.write([item]);

            this.copiedBlob = url;
            setTimeout(() => this.copiedBlob = false, 2500);
        } catch (err) {
            console.error('Binary image copy failed:', err);
            this.copyUrl(url);
        }
    },
    imageUrlToPngBlob(url) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.naturalWidth || img.width || 300;
                    canvas.height = img.naturalHeight || img.height || 300;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    canvas.toBlob((blob) => {
                        if (blob) {
                            resolve(blob);
                        } else {
                            reject(new Error('Canvas toBlob failed'));
                        }
                    }, 'image/png');
                } catch (e) {
                    reject(e);
                }
            };
            img.onerror = () => reject(new Error('Image failed to load via proxy'));
            img.src = url;
        });
    },
    copyUrl(url) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(() => {
                this.copiedImg = url;
                setTimeout(() => this.copiedImg = false, 2000);
            }).catch(() => {
                this.fallbackCopyUrl(url);
            });
        } else {
            this.fallbackCopyUrl(url);
        }
    },
    fallbackCopy(textToCopy) {
        let temp = document.createElement('textarea');
        temp.value = textToCopy;
        temp.style.position = 'fixed';
        temp.style.opacity = '0';
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        this.copied = true;
        setTimeout(() => this.copied = false, 2000);
    },
    fallbackCopyUrl(url) {
        let temp = document.createElement('textarea');
        temp.value = url;
        temp.style.position = 'fixed';
        temp.style.opacity = '0';
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        this.copiedImg = url;
        setTimeout(() => this.copiedImg = false, 2000);
    }
}" class="space-y-4">

    @php
        $productThumbnails = $get('productThumbnails') ?? [];
        $groupedPhotos = collect($productThumbnails)->groupBy('product_name');
    @endphp

    <!-- SEÇÃO SUPERIOR: Botões de Ação Rápida e Fotos Anexadas dos Produtos (Mobile & Desktop) -->
    <div class="space-y-3 whatsapp-preview-copy">
        
        <div class="flex items-center justify-between bg-emerald-50 dark:bg-emerald-950/40 p-2.5 rounded-xl border border-emerald-200/60 dark:border-emerald-800/40">
            <span class="text-xs font-semibold text-emerald-900 dark:text-emerald-200 flex items-center gap-1.5">
                <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                Ações & Anexos da Proposta
            </span>
            <button
                type="button"
                x-on:click="copyText()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-lg shadow-sm transition-all duration-150 cursor-pointer shrink-0"
            >
                <x-filament::icon icon="heroicon-o-document-duplicate" class="w-4 h-4" />
                <span x-text="copied ? '✓ Copiado!' : 'Copiar Texto'">Copiar Texto</span>
            </button>
        </div>

        @if ($groupedPhotos->isNotEmpty())
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-3 space-y-3 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <x-filament::icon icon="heroicon-o-photo" class="w-3.5 h-3.5 text-emerald-500" />
                        Fotos dos Produtos ({{ count($productThumbnails) }})
                    </span>
                    <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500">
                        Clique na foto para copiar
                    </span>
                </div>

                <div class="space-y-3 max-h-52 overflow-y-auto pr-1">
                    @foreach ($groupedPhotos as $productName => $photos)
                        <div class="space-y-1.5">
                            <h5 class="text-[11px] font-bold text-slate-700 dark:text-slate-300 truncate flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="truncate">{{ $productName }}</span>
                                <span class="text-[10px] text-slate-400 font-normal">({{ count($photos) }})</span>
                            </h5>
                            
                            <div class="flex flex-wrap gap-2">
                                @foreach ($photos as $thumb)
                                    <div 
                                        type="button"
                                        x-on:click="copyImage('{{ $thumb['image_url'] }}')"
                                        class="group relative w-12 h-12 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 overflow-hidden shadow-xs cursor-pointer select-none shrink-0"
                                        title="{{ $thumb['product_name'] }}"
                                    >
                                        <!-- Imagem Thumbnail -->
                                        <img 
                                            src="{{ $thumb['image_url'] }}" 
                                            alt="{{ $thumb['product_name'] }}" 
                                            class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-110" 
                                        />

                                        <!-- Overlay no Hover (Desktop) e Estado de Cópia (Desktop/Mobile) -->
                                        <div 
                                            class="absolute inset-0 transition-all duration-150 flex flex-col items-center justify-center p-0.5 text-center text-white"
                                            :class="copiedBlob === '{{ $thumb['image_url'] }}' 
                                                ? 'bg-emerald-600/90 opacity-100' 
                                                : 'bg-black/60 opacity-0 group-hover:opacity-100'"
                                        >
                                            <template x-if="copiedBlob !== '{{ $thumb['image_url'] }}'">
                                                <div class="flex flex-col items-center gap-0.5">
                                                    <x-filament::icon icon="heroicon-o-clipboard-document" class="w-3.5 h-3.5 text-white" />
                                                    <span class="text-[8px] font-bold leading-none tracking-tight">Copiar</span>
                                                </div>
                                            </template>
                                            <template x-if="copiedBlob === '{{ $thumb['image_url'] }}'">
                                                <div class="flex flex-col items-center gap-0.5">
                                                    <x-filament::icon icon="heroicon-o-check" class="w-3.5 h-3.5 text-white" />
                                                    <span class="text-[8px] font-extrabold leading-none tracking-tight">Copiada!</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    <!-- SEÇÃO INFERIOR: Pré-visualização com Design Autêntico do WhatsApp Web (EXIBIDA APENAS NO DESKTOP) -->
    <div class="whatsapp-preview-container space-y-2 pt-1">
        
        <div class="rounded-2xl overflow-hidden border border-emerald-600/30 dark:border-emerald-500/30 shadow-lg bg-[#efeae2] dark:bg-[#0b141a]">
            
            <!-- Header do WhatsApp -->
            <div class="bg-[#075e54] dark:bg-[#111b21] px-3.5 py-2 text-white flex items-center justify-between border-b border-emerald-800/40">
                <div class="flex items-center gap-2.5">
                    <div class="relative">
                        <div class="w-8 h-8 rounded-full bg-emerald-700 dark:bg-emerald-800 flex items-center justify-center font-bold text-xs text-white shadow-inner">
                            💬
                        </div>
                        <span class="absolute bottom-0 right-0 w-2 h-2 bg-emerald-400 border-2 border-[#075e54] rounded-full"></span>
                    </div>
                    <div>
                        <h4 class="font-bold text-xs leading-tight text-white flex items-center gap-1.5">
                            Prévia em Tempo Real (WhatsApp)
                        </h4>
                        <p class="text-[10px] text-emerald-100/80 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Atualiza ao editar o texto à esquerda
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    x-on:click="copyText()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 rounded-lg shadow-sm transition-all duration-150 cursor-pointer shrink-0"
                    title="Copiar Texto da Proposta"
                >
                    <x-filament::icon icon="heroicon-o-document-duplicate" class="w-4 h-4 text-white" />
                    <span x-text="copied ? '✓ Copiado!' : 'Copiar'">Copiar</span>
                </button>
            </div>

            <!-- Wallpaper do WhatsApp com o Balão Reativo em Tempo Real -->
            <div class="p-3.5 space-y-3 min-h-[260px] max-h-[420px] overflow-y-auto bg-[radial-gradient(#0000000a_1px,transparent_1px)] dark:bg-[radial-gradient(#ffffff0a_1px,transparent_1px)] [background-size:16px_16px]">
                
                <div class="flex justify-center my-0.5">
                    <span class="px-2.5 py-0.5 text-[9px] font-semibold tracking-wide uppercase bg-white/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 rounded-full shadow-xs border border-slate-200/50 dark:border-slate-700/50">
                        Hoje
                    </span>
                </div>

                <!-- Balão de Mensagem com Texto Atualizado em Tempo Real -->
                <div class="flex justify-end">
                    <div class="max-w-[95%] rounded-2xl rounded-tr-xs bg-[#d9fdd3] dark:bg-[#005c4b] text-slate-800 dark:text-slate-100 p-3 shadow-xs border border-emerald-200/40 dark:border-emerald-600/30 space-y-2">
                        
                        <!-- Texto Reativo do WhatsApp -->
                        <div class="text-xs leading-relaxed whitespace-pre-line font-sans break-words select-text" x-text="message || 'Digite a mensagem na caixa de texto à esquerda...'"></div>

                        <!-- Horário e Double Checkmark Azul -->
                        <div class="flex items-center justify-end gap-1 text-[10px] text-slate-500 dark:text-emerald-200/70 pt-0.5">
                            <span>{{ now()->format('H:i') }}</span>
                            <svg class="w-3.5 h-3.5 text-[#53bdeb]" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.3 6.3a1 1 0 0 0-1.4 0l-6.6 6.6-2.6-2.6a1 1 0 0 0-1.4 1.4l3.3 3.3a1 1 0 0 0 1.4 0l7.3-7.3a1 1 0 0 0 0-1.4zm4.2 0a1 1 0 0 0-1.4 0l-10.8 10.8-1.5-1.5a1 1 0 1 0-1.4 1.4l2.2 2.2a1 1 0 0 0 1.4 0l11.5-11.5a1 1 0 0 0 0-1.4z"/>
                            </svg>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>

    <style>
        @media (max-width: 767px) {
            .whatsapp-preview-container {
                display: none !important;
            }
        }
        @media (min-width: 768px) {
            .whatsapp-preview-container {
                display: block !important;
            }
        }

        @media (max-width: 767px) {
            .whatsapp-preview-copy {
                display: block !important;
            }
        }
        @media (min-width: 768px) {
            .whatsapp-preview-copy {
                display: none !important;
            }
        }
    </style>
</div>
