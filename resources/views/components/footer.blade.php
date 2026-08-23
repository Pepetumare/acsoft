<footer class="footer-acsoft">
    <div class="container py-5">

        <div class="row g-4 align-items-start">

            <div class="col-lg-5">

                <a class="footer-brand text-decoration-none" href="{{ route('home') }}">
                    <span>AC</span>Soft
                </a>

                <p class="footer-description mt-3 mb-0">
                    Software simple para negocios reales.
                    Digitalizamos procesos cotidianos con herramientas web claras,
                    rápidas y fáciles de utilizar.
                </p>

            </div>

            <div class="col-6 col-lg-3">

                <h6 class="footer-title">
                    ACSoft
                </h6>

                <ul class="list-unstyled footer-links">
                    <li>
                        <a href="#solucion">Solución</a>
                    </li>

                    <li>
                        <a href="#caracteristicas">Características</a>
                    </li>

                    <li>
                        <a href="#servicios">Servicios</a>
                    </li>

                    <li>
                        <a href="#contacto">Contacto</a>
                    </li>
                </ul>

            </div>

            <div class="col-6 col-lg-4">

                <h6 class="footer-title">
                    Prueba el sistema
                </h6>

                <p class="footer-small">
                    Conoce cómo funciona nuestro sistema de recepción de mercadería.
                </p>

                <a href="{{ route('demo.index') }}" class="btn btn-outline-light">
                    Entrar a la demo
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
