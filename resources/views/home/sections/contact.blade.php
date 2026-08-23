<section class="contact-section py-5 py-lg-7" id="contacto">

    <div class="container">

        <div class="contact-wrapper">

            <div class="row g-5 align-items-start">

                {{-- TEXTO --}}
                <div class="col-lg-5">

                    <span class="section-eyebrow">
                        Contacto
                    </span>

                    <h2 class="section-title mt-3">
                        Cuéntame qué proceso quieres
                        <span class="text-acsoft-accent">
                            ordenar en tu negocio.
                        </span>
                    </h2>

                    <p class="section-description mt-3">
                        Explícame cómo trabajas actualmente y qué problema
                        te gustaría resolver. Podemos evaluar una solución
                        simple y adaptada a tu negocio.
                    </p>


                    <div class="contact-help mt-4">

                        <div class="contact-help-item">

                            <div class="contact-help-icon">
                                01
                            </div>

                            <div>
                                <strong>
                                    Cuéntame el problema
                                </strong>

                                <span>
                                    No necesitas saber de tecnología.
                                    Solo explica cómo realizas hoy el proceso.
                                </span>
                            </div>

                        </div>


                        <div class="contact-help-item">

                            <div class="contact-help-icon">
                                02
                            </div>

                            <div>
                                <strong>
                                    Revisamos una posible solución
                                </strong>

                                <span>
                                    Evaluamos si un sistema web puede ayudarte
                                    a simplificar el trabajo.
                                </span>
                            </div>

                        </div>


                        <div class="contact-help-item">

                            <div class="contact-help-icon">
                                03
                            </div>

                            <div>
                                <strong>
                                    Diseñamos algo útil
                                </strong>

                                <span>
                                    La solución se construye alrededor del
                                    proceso real del negocio.
                                </span>
                            </div>

                        </div>

                    </div>


                    <div class="contact-whatsapp mt-4">

                        <div>

                            <span class="contact-whatsapp-label">
                                ¿Prefieres conversar directamente?
                            </span>

                            <strong>
                                Hablemos por WhatsApp
                            </strong>

                        </div>

                        <x-whatsapp-button label="WhatsApp" />

                    </div>

                </div>


                {{-- FORMULARIO --}}
                <div class="col-lg-7">

                    <div class="contact-form-card">

                        <div class="contact-form-header">

                            <span>
                                Solicitud de información
                            </span>

                            <h3>
                                ¿En qué puedo ayudarte?
                            </h3>

                            <p>
                                Completa estos datos y responderé tu consulta.
                            </p>

                        </div>


                        @if (session('contact_success'))
                            <div class="alert alert-success contact-success" role="alert">
                                <strong>
                                    ¡Consulta enviada!
                                </strong>

                                <span>
                                    {{ session('contact_success') }}
                                </span>
                            </div>
                        @endif


                        <form action="{{ route('contact.store') }}" method="POST" class="contact-form"
                            id="contactForm">

                            @csrf
                            <div class="d-none" aria-hidden="true">
                                <label for="website">Website</label>

                                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="row g-3">

                                <div class="col-md-6">

                                    <label for="name" class="form-label">
                                        Nombre
                                        <span>*</span>
                                    </label>

                                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                                        class="form-control
                                            @error('name') is-invalid @enderror"
                                        placeholder="Tu nombre" autocomplete="name" required>

                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                <div class="col-md-6">

                                    <label for="business" class="form-label">
                                        Negocio
                                    </label>

                                    <input type="text" id="business" name="business" value="{{ old('business') }}"
                                        class="form-control
                                            @error('business') is-invalid @enderror"
                                        placeholder="Ej: Minimarket Central" autocomplete="organization">

                                    @error('business')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                <div class="col-12">

                                    <label for="contact" class="form-label">
                                        Teléfono o correo
                                        <span>*</span>
                                    </label>

                                    <input type="text" id="contact" name="contact" value="{{ old('contact') }}"
                                        class="form-control
                                            @error('contact') is-invalid @enderror"
                                        placeholder="+56 9... o correo@ejemplo.cl" required>

                                    @error('contact')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                <div class="col-12">

                                    <label for="message" class="form-label">
                                        ¿En qué necesitas ayuda?
                                        <span>*</span>
                                    </label>

                                    <textarea id="message" name="message" rows="6"
                                        class="form-control
                                            @error('message') is-invalid @enderror"
                                        placeholder="Por ejemplo: actualmente anotamos los pedidos en un cuaderno y después los pasamos a una planilla..."
                                        required>{{ old('message') }}</textarea>

                                    @error('message')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                <div class="col-12">

                                    <div class="contact-form-footer">

                                        <p>
                                            Al enviar este formulario,
                                            tus datos serán utilizados únicamente
                                            para responder tu consulta.
                                        </p>

                                        <button type="submit" class="btn btn-acsoft-primary btn-lg px-4"
                                            id="contactSubmit">
                                            <span class="contact-submit-text">
                                                Enviar consulta →
                                            </span>

                                            <span class="contact-submit-loading d-none">
                                                Enviando...
                                            </span>
                                        </button>

                                    </div>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

    @if ($errors->any() || session('contact_success'))
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {

                    const form = document.getElementById('contactForm');
                    const button = document.getElementById('contactSubmit');

                    if (!form || !button) {
                        return;
                    }

                    form.addEventListener('submit', function() {

                        if (!form.checkValidity()) {
                            return;
                        }

                        button.disabled = true;

                        button
                            .querySelector('.contact-submit-text')
                            ?.classList.add('d-none');

                        button
                            .querySelector('.contact-submit-loading')
                            ?.classList.remove('d-none');
                    });

                });
            </script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    document
                        .getElementById('contacto')
                        ?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                });
            </script>
        @endpush
    @endif

</section>
