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
                        Solicita una demostración y conoce
                        <span class="text-acsoft-accent">
                            ACSoft en tu operación.
                        </span>
                    </h2>

                    <p class="section-description mt-3">
                        Te crearemos un usuario temporal para que uses el sistema real
                        con un negocio de prueba durante aproximadamente 5 días.
                    </p>


                    <div class="contact-help mt-4">

                        <div class="contact-help-item">

                            <div class="contact-help-icon">
                                01
                            </div>

                            <div>
                                <strong>
                                    Envía tu solicitud
                                </strong>

                                <span>
                                    Cuéntanos sobre tu negocio y deja un medio de contacto.
                                </span>
                            </div>

                        </div>


                        <div class="contact-help-item">

                            <div class="contact-help-icon">
                                02
                            </div>

                            <div>
                                <strong>
                                    Coordinamos contigo
                                </strong>

                                <span>
                                    Conversamos brevemente para entender qué necesitas probar.
                                </span>
                            </div>

                        </div>


                        <div class="contact-help-item">

                            <div class="contact-help-icon">
                                03
                            </div>

                            <div>
                                <strong>
                                    Preparamos tu acceso
                                </strong>

                                <span>
                                    Recibirás un usuario temporal y un negocio de prueba.
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
                                Solicitud de demostración
                            </span>

                            <h3>
                                Cuéntanos sobre tu negocio
                            </h3>

                            <p>
                                Completa estos datos y te contactaremos.
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

                                    <label for="email" class="form-label">
                                        Correo
                                        <span>*</span>
                                    </label>

                                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                                        class="form-control
                                            @error('email') is-invalid @enderror"
                                        placeholder="correo@ejemplo.cl" autocomplete="email" required>

                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                                <div class="col-12">
                                    <label for="phone" class="form-label">Teléfono <small class="text-muted">(opcional)</small></label>
                                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="+56 9..." autocomplete="tel">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>


                                <div class="col-12">

                                    <label for="message" class="form-label">
                                        ¿Qué te gustaría probar o resolver?
                                        <span>*</span>
                                    </label>

                                    <textarea id="message" name="message" rows="6"
                                        class="form-control
                                            @error('message') is-invalid @enderror"
                                        placeholder="Por ejemplo: necesito ordenar las ventas, controlar caja y conocer el stock disponible..."
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
                                                Enviar solicitud →
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
