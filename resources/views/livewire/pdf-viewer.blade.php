<div>
    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf_viewer.min.css">
    <style>
        .pdf-viewer-container {
            position: relative;
            width: 100%;
        }

        .pdf-toolbar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            z-index: 100;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .page-container {
            margin: 20px auto;
            position: relative;
            width: 100%;
            padding: 0 0;
        }

        .page-number {
            text-align: center;
            padding: 8px;
            background: #f3f4f6;
            border-radius: 4px 4px 0 0;
            border: 1px solid #e5e7eb;
            border-bottom: none;
            font-size: 14px;
            color: #374151;
        }

        .page-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-number-input {
            width: 60px;
            text-align: center;
            padding: 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .button {
            padding: 6px 12px;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .button:hover {
            background: #e5e7eb;
        }

        .button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page-wrapper {
            position: relative;
            background: white;
            margin: 0 auto;
            width: 100%;
            display: flex;
            justify-content: center;
            overflow: hidden;
        }

    </style>
    @endpush

    <div id="container-{{ $viewerId }}" class="pdf-viewer-container {{ $containerClass }}">
        <div id="loading-{{ $viewerId }}" class="pdf-loading">Loading PDF...</div>

        @if($showControls)
        <div id="toolbar-{{ $viewerId }}" class="pdf-toolbar">
            <div class="page-controls">
                <button id="prev-{{ $viewerId }}" class="button" disabled>Previous</button>
                <input type="number" id="current-page-{{ $viewerId }}" class="page-number-input" value="1" min="1">
                <span>of</span>
                <span id="page-count-{{ $viewerId }}">0</span>
                <button id="next-{{ $viewerId }}" class="button" disabled>Next</button>
            </div>
        </div>
        @endif

        <div class="pdf-content">
            <div id="{{ $viewerId }}" class="pdfViewer"></div>
        </div>
    </div>

    @push('scripts')
    <script src="/js/pdf.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewer = {
                container: document.getElementById('container-{{ $viewerId }}'),
                viewerElement: document.getElementById('{{ $viewerId }}'),
                loading: document.getElementById('loading-{{ $viewerId }}'),
                currentPageInput: document.getElementById('current-page-{{ $viewerId }}'),
                pageCount: document.getElementById('page-count-{{ $viewerId }}'),
                prevButton: document.getElementById('prev-{{ $viewerId }}'),
                nextButton: document.getElementById('next-{{ $viewerId }}'),
                pdfDoc: null,
                currentPageNumber: 1,
                loadingTask: null,
                showAllPages: {{ $showAllPages ? 'true' : 'false' }},
                pageRendering: false,
                pageNumPending: null,
                canvases: [],
                textLayers: [],
                viewerId: '{{ $viewerId }}',

                async init() {
                    try {
                        pdfjsLib.GlobalWorkerOptions.workerSrc = '/js/pdf.worker.min.js';
                        await this.loadPDF('{{ $url }}');
                        this.setupListeners();
                        await this.renderPDF();
                    } catch (error) {
                        console.error('Initialization error:', error);
                    }
                },

                showLoading() {
                    this.loading.style.display = 'block';
                },

                hideLoading() {
                    this.loading.style.display = 'none';
                },

                async loadPDF(url) {
                    this.showLoading();
                    try {
                        this.loadingTask = pdfjsLib.getDocument({
                            url: url,
                            cMapPacked: true,
                        });

                        this.pdfDoc = await this.loadingTask.promise;
                        this.pageCount.textContent = this.pdfDoc.numPages;
                        this.currentPageInput.max = this.pdfDoc.numPages;
                        this.updateUI();
                        this.hideLoading();
                    } catch (error) {
                        console.error('Error loading PDF:', error);
                        this.hideLoading();
                    }
                },

                setupListeners() {
                    if (this.prevButton) {
                        this.prevButton.addEventListener('click', () => this.goPreviousPage());
                    }
                    if (this.nextButton) {
                        this.nextButton.addEventListener('click', () => this.goNextPage());
                    }
                    if (this.currentPageInput) {
                        this.currentPageInput.addEventListener('change', (e) => this.goToPage(parseInt(e.target.value)));
                    }
                },

                updateUI() {
                    if (this.prevButton) {
                        this.prevButton.disabled = this.currentPageNumber <= 1;
                    }
                    if (this.nextButton) {
                        this.nextButton.disabled = this.currentPageNumber >= this.pdfDoc.numPages;
                    }
                    if (this.currentPageInput) {
                        this.currentPageInput.value = this.currentPageNumber;
                    }
                },

                async renderPDF() {
                    if (this.showAllPages) {
                        await this.renderAllPages();
                    } else {
                        await this.renderCurrentPage();
                    }
                },

                async renderAllPages() {
                    try {
                        this.viewerElement.innerHTML = '';
                        this.canvases = [];
                        this.textLayers = [];
                        const totalPages = this.pdfDoc.numPages;

                        for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                            const pageContainer = document.createElement('div');
                            pageContainer.className = 'page-container';

                            // if (pageNum === this.currentPageNumber) {
                            //     pageContainer.style.boxShadow = '0 0 0 2px #444';
                            // }

                            const pageWrapper = document.createElement('div');
                            pageWrapper.className = 'page-wrapper';

                            const pageNumber = document.createElement('div');
                            pageNumber.className = 'page-number';
                            pageNumber.textContent = `Page ${pageNum}`;

                            const canvasWrapper = document.createElement('div');
                            canvasWrapper.style.position = 'relative';
                            canvasWrapper.style.width = 'fit-content';

                            const canvas = document.createElement('canvas');
                            canvas.id = `page-${pageNum}-${this.viewerId}`;

                            const textLayer = document.createElement('div');
                            textLayer.className = 'textLayer';

                            canvasWrapper.appendChild(canvas);
                            canvasWrapper.appendChild(textLayer);
                            pageWrapper.appendChild(canvasWrapper);

                            // pageContainer.appendChild(pageNumber);
                            pageContainer.appendChild(pageWrapper);
                            this.viewerElement.appendChild(pageContainer);

                            this.canvases.push(canvas);
                            this.textLayers.push(textLayer);

                            await this.renderPage(pageNum);
                        }
                    } catch (error) {
                        console.error('Error rendering all pages:', error);
                    }
                },

                async renderPage(pageNum) {
                    try {
                        const page = await this.pdfDoc.getPage(pageNum);
                        const viewport = page.getViewport({ scale: 1 });

                        const canvas = this.canvases[pageNum - 1];
                        const textLayer = this.textLayers[pageNum - 1];

                        if (!canvas || !textLayer) {
                            console.error('Canvas or text layer not found');
                            return;
                        }

                        const context = canvas.getContext('2d');
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        // Set text layer dimensions to match canvas
                        textLayer.style.width = `${viewport.width}px`;
                        textLayer.style.height = `${viewport.height}px`;

                        const renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };

                        await page.render(renderContext).promise;

                        const textContent = await page.getTextContent();
                        textLayer.innerHTML = '';

                        // Ensure text layer is properly positioned
                        await pdfjsLib.renderTextLayer({
                            textContentSource: textContent,
                            container: textLayer,
                            viewport: viewport,
                            textDivs: [],
                            enhanceTextSelection: true
                        }).promise;

                    } catch (error) {
                        console.error(`Error rendering page ${pageNum}:`, error);
                    }
                },

                async renderCurrentPage() {
                    if (this.pageRendering) {
                        this.pageNumPending = this.currentPageNumber;
                        return;
                    }

                    this.pageRendering = true;

                    try {
                        const page = await this.pdfDoc.getPage(this.currentPageNumber);
                        const viewport = page.getViewport({ scale: 1 });

                        this.viewerElement.innerHTML = '';

                        const pageContainer = document.createElement('div');
                        pageContainer.className = 'page-container';

                        const pageWrapper = document.createElement('div');
                        pageWrapper.className = 'page-wrapper';
                        pageWrapper.style.position = 'relative';

                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d');
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        // For single page view, we skip text layer rendering
                        pageWrapper.appendChild(canvas);
                        pageContainer.appendChild(pageWrapper);
                        this.viewerElement.appendChild(pageContainer);

                        const renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };

                        await page.render(renderContext).promise;

                        this.pageRendering = false;
                        if (this.pageNumPending !== null) {
                            const pageNum = this.pageNumPending;
                            this.pageNumPending = null;
                            this.currentPageNumber = pageNum;
                            await this.renderCurrentPage();
                        }
                    } catch (error) {
                        console.error('Error rendering page:', error);
                        this.pageRendering = false;
                    }
                },

                async goToPage(pageNumber) {
                    if (!this.pdfDoc) return;

                    const pageCount = this.pdfDoc.numPages;
                    this.currentPageNumber = Math.max(1, Math.min(pageCount, pageNumber));

                    if (this.showAllPages) {
                        const targetPage = document.getElementById(`page-${this.currentPageNumber}-${this.viewerId}`);
                        if (targetPage) {
                            document.querySelectorAll('.page-container').forEach(container => {
                                container.style.boxShadow = 'none';
                            });

                            targetPage.closest('.page-container').style.boxShadow = '0 0 0 2px #444';
                            targetPage.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    } else {
                        await this.renderCurrentPage();
                    }
                    this.updateUI();
                },

                async goPreviousPage() {
                    if (this.currentPageNumber <= 1) return;
                    await this.goToPage(this.currentPageNumber - 1);
                },

                async goNextPage() {
                    if (!this.pdfDoc || this.currentPageNumber >= this.pdfDoc.numPages) return;
                    await this.goToPage(this.currentPageNumber + 1);
                }
            };

            viewer.init();
        });
    </script>
    @endpush
</div>
