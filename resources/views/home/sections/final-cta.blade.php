<section class="final-cta-section">
    <div class="container">

        <div class="final-cta-panel">

            <div class="row align-items-center g-4">

                <div class="col-lg-8">

                    <span class="final-cta-eyebrow">
                        ACSoft
                    </span>

                    <h2>
                        Tu negocio no necesita un sistema más complicado.
                        <span>Necesita uno que realmente te sirva.</span>
                    </h2>

                    <p>
                        Solicita una demostración. Prepararemos un usuario temporal para que
                        conozcas el sistema real con un negocio de prueba.
                    </p>

                </div>

                <div class="col-lg-4">

                    <div class="final-cta-actions">

                        <a href="{{ route('contacto') }}" class="btn btn-final-primary btn-lg">
                            Solicitar demostración
                            <span aria-hidden="true">→</span>
                        </a>

                        {{-- <a
                            href="{{ route('home') }}#contacto"
                            class="btn btn-final-secondary btn-lg"
                        >
                            Hablemos de tu negocio
                        </a> --}}

                        <x-whatsapp-button label="Hablemos de tu negocio" class="btn btn-final-secondary btn-lg"
                            message="Hola, vi ACSoft y quisiera conversar sobre una solución para mi negocio." />
                    </div>

                </div>

            </div>

        </div>

    </div>
</section>
