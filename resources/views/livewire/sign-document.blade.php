<div>
    <style>
        .docusign-agreement {
            width: 100%;
            height: calc(100vh - 100px);
        }
    </style>

    <div>{{ $document->name }}</div>

    <div class="docusign-agreement" id="agreement"></div>

    @assets
    <script src='https://js.docusign.com/bundle.js'></script>
    @endassets

    @script
    <script>
        window.DocuSign.loadDocuSign("4065f0a2-dd9f-44d9-bcbb-b9851f14b15e")
            .then((docusign) => {
                const signing = docusign.signing({
                    url: "{{ $this->docusign_url }}",
                    displayFormat: 'focused',
                    style: {
                        branding: {
                            primaryButton: {
                                backgroundColor: '#333',
                                color: '#fff',
                            }
                        },

                        signingNavigationButton: {
                            finishText: 'You have finished the document! Hooray!',
                            // 'bottom-left'|'bottom-center'|'bottom-right',  default: bottom-right
                            position: 'bottom-center'
                        }
                    }
                });

                signing.on('ready', (event) => {
                    console.log('UI is rendered');
                });

                signing.on('sessionEnd', (event) => {
                    console.log('sessionend', event);
                    if (event.sessionEndType == 'signing_complete') {
                        $wire.dispatch('document-Signed');
                    }
                });

                signing.mount('#agreement');
            })
            .catch((ex) => {
                // Any configuration or API limits will be caught here
            });
    </script>
    @endscript
</div>
