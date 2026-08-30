<footer class="footer-acsoft">
    <div class="container py-5">

        <div class="row g-4 align-items-start">

            <div class="col-lg-4">

                <a class="footer-brand text-decoration-none" href="{{ route('home') }}">
                    <span>AC</span>Soft
                </a>

                <p class="footer-description mt-3 mb-0">
                    Gestión simple para negocios reales. Ventas, caja, productos y stock en un solo lugar.
                </p>

            </div>

            <div class="col-6 col-lg-2">

                <h6 class="footer-title">
                    ACSoft
                </h6>

                <ul class="list-unstyled footer-links">
                    <li>
                        <a href="{{ route('home') }}">Inicio</a>
                    </li>

                    <li>
                        <a href="{{ route('functions') }}">Funciones</a>
                    </li>

                    <li>
                        <a href="{{ route('pricing') }}">Precios</a>
                    </li>

                    <li>
                        <a href="{{ route('contacto') }}">Contacto</a>
                    </li>
                    <li><a href="{{ route('login') }}">Iniciar sesión</a></li>
                </ul>

            </div>

            <div class="col-6 col-lg-3">

                <h6 class="footer-title">Legal</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="{{ route('privacy') }}">Política de privacidad</a></li>
                    <li><a href="{{ route('terms') }}">Términos</a></li>
                </ul>

            </div>

            <div class="col-12 col-lg-3">

                <h6 class="footer-title">
                    Conoce ACSoft
                </h6>

                <p class="footer-small">
                    Solicita una demostración y te prepararemos un acceso temporal con un negocio de prueba.
                </p>

                <a href="{{ route('contacto') }}" class="btn btn-outline-light">
                    Solicitar demostración
                </a>

            </div>

        </div>

        <hr class="footer-divider">

        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 footer-bottom">

            <span>
                © {{ date('Y') }} ACSoft. Todos los derechos reservados.
            </span>

            <div class="footer-contact-links">

                <span>
                    {{ config('acsoft.domain') }}
                </span>

                @if (config('acsoft.email'))
                    <a href="mailto:{{ config('acsoft.email') }}">
                        {{ config('acsoft.email') }}
                    </a>
                @endif

            </div>

        </div>

    </div>
</footer>
