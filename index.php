<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CSM Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="icon" href="/CBMMT_00.png" sizes="32x32" type="image/png">
    <link rel="icon" href="/CBMMT_00.png" sizes="16x16" type="image/png">
    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem
            }
        }

        .b-example-divider {
            width: 100%;
            height: 3rem;
            background-color: #0000001a;
            border: solid rgba(0, 0, 0, .15);
            border-width: 1px 0;
            box-shadow: inset 0 .5em 1.5em #0000001a, inset 0 .125em .5em #00000026
        }

        .b-example-vr {
            flex-shrink: 0;
            width: 1.5rem;
            height: 100vh
        }

        .bi {
            vertical-align: -.125em;
            fill: currentColor
        }

        .nav-scroller {
            position: relative;
            z-index: 2;
            height: 2.75rem;
            overflow-y: hidden
        }

        .nav-scroller .nav {
            display: flex;
            flex-wrap: nowrap;
            padding-bottom: 1rem;
            margin-top: -1px;
            overflow-x: auto;
            text-align: center;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch
        }

        .btn-bd-primary {
            --bd-violet-bg: #712cf9;
            --bd-violet-rgb: 112.520718, 44.062154, 249.437846;
            --bs-btn-font-weight: 600;
            --bs-btn-color: var(--bs-white);
            --bs-btn-bg: var(--bd-violet-bg);
            --bs-btn-border-color: var(--bd-violet-bg);
            --bs-btn-hover-color: var(--bs-white);
            --bs-btn-hover-bg: #6528e0;
            --bs-btn-hover-border-color: #6528e0;
            --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
            --bs-btn-active-color: var(--bs-btn-hover-color);
            --bs-btn-active-bg: #5a23c8;
            --bs-btn-active-border-color: #5a23c8
        }

        .bd-mode-toggle {
            z-index: 1500
        }

        .bd-mode-toggle .bi {
            width: 1em;
            height: 1em
        }

        .bd-mode-toggle .dropdown-menu .active .bi {
            display: block !important
        }
    </style>
</head>

<body>
    <div class="container py-3">
        <header>
            <div class="d-flex flex-column flex-md-row align-items-center pb- mb-4 border-bottom">
                <a href="/" class="d-flex align-items-center link-body-emphasis text-decoration-none"><img class="img-fluid mx-auto d-none d-sm-block mb-3" src="/assets/images/CBMMT_00.png" alt="Bootstrap" width="40">&nbsp;&nbsp;
                    <span class="fs-4">Manutenção - CBMMT</span>
                </a>
                <nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
                    <!-- <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="#">Features</a>
                    <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="#">Enterprise</a>
                    <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="#">Support</a> -->
                    <a class="py-2 link-body-emphasis text-decoration-none" href="/login.php"><button class="btn btn-outline-danger w-100" type="submit">Acesse</button></a>
                </nav>
            </div>
            <div class="pricing-header p-3 pb-md-4 mx-auto text-center">
                <h1 class="display-4 fw-normal text-body-emphasis">Gestão de Frota</h1>
                <p class="fs-5 text-body-secondary">Quickly build an effective pricing table for your potential customers with this Bootstrap example. It’s built with default Bootstrap components and utilities with little customization.</p>
            </div>
        </header>
        <main>
            <div class="row row-cols-1 row-cols-md-3 mb-3 text-center">
                <div class="col">
                    <div class="card mb-4 rounded-3 shadow-sm border-danger">
                        <div class="card-header py-3 text-bg-danger border-danger">
                            <h4 class="my-0 fw-normal">
                                <i class="bi bi-truck" style="font-size: 2rem;"></i>
                                <span class="text-body-primary">Viaturas</span>
                            </h4>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title pricing-card-title">Controle de frota</h3>
                            <span class="text-body-secondary">
                                <p style="text-align: justify;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum finibus erat eros, eu volutpat lectus suscipit vitae. Fusce congue facilisis purus, a sodales magna.</p>
                            </span>
                            <!-- <ul class="list-unstyled mt-3 mb-4">
                                <li>10 users included</li>
                                <li>2 GB of storage</li>
                                <li>Email support</li>
                                <li>Help center access</li>
                            </ul> -->
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card mb-4 rounded-3 shadow-sm border-danger">
                        <div class="card-header py-3 text-bg-danger border-danger">
                            <h4 class="my-0 fw-normal">
                                <i class="bi bi-file-earmark-bar-graph" style="font-size: 2rem;"></i>
                                <span class="text-body-primary">Estatísticas</span>
                            </h4>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title pricing-card-title">Informações precisas</h3>
                            <span class="text-body-secondary">
                                <p style="text-align: justify;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum finibus erat eros, eu volutpat lectus suscipit vitae. Fusce congue facilisis purus, a sodales magna.</p>
                            </span>
                            <!-- <ul class="list-unstyled mt-3 mb-4">
                                <li>10 users included</li>
                                <li>2 GB of storage</li>
                                <li>Email support</li>
                                <li>Help center access</li>
                            </ul> -->
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card mb-4 rounded-3 shadow-sm border-danger">
                        <div class="card-header py-3 text-bg-danger border-danger">
                            <h4 class="my-0 fw-normal">
                                <i class="bi bi-door-open" style="font-size: 2rem;"></i>
                                <span class="text-body-primary">Segurança</span>
                            </h4>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title pricing-card-title">Dados confiáveis</h3>
                            <span class="text-body-secondary">
                                <p style="text-align: justify;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum finibus erat eros, eu volutpat lectus suscipit vitae. Fusce congue facilisis purus, a sodales magna.</p>
                            </span>
                            <!-- <ul class="list-unstyled mt-3 mb-4">
                                <li>10 users included</li>
                                <li>2 GB of storage</li>
                                <li>Email support</li>
                                <li>Help center access</li>
                            </ul> -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-3 text-center">
                <div class="col">
                    <h1 class="cover-heading">Sobre o sistema</h1>
                    <p class="lead">O SISMAN é uma ferramenta desenvolvida para atender às necessidades de gestão de informações de frota do CBMMT, e visa proporcionar maior eficiência, transparência e controle.</p>
                </div>
            </div>
        </main>
        <footer class="pt-4 my-md-5 pt-md-5 border-top">
            <div class="row">
                <div class="col-12 col-md"> <img class="mb-2" src="/assets/images/CBMMT_00.png" alt="" width="24"> <small class="d-block mb-3 text-body-secondary">&copy; 2017–2025</small> </div>
                <div class="col-6 col-md">
                    <h5>Features</h5>
                    <ul class="list-unstyled text-small">
                        <li class="mb-1"><a class="link-secondary text-decoration-none" href="#">Cool stuff</a></li>
                        <li class="mb-1"><a class="link-secondary text-decoration-none" href="#">Random feature</a></li>
                        <li class="mb-1"><a class="link-secondary text-decoration-none" href="#">Team feature</a></li>
                        <li class="mb-1"><a class="link-secondary text-decoration-none" href="#">Stuff for developers</a></li>
                        <li class="mb-1"><a class="link-secondary text-decoration-none" href="#">Another one</a></li>
                        <li class="mb-1"><a class="link-secondary text-decoration-none" href="#">Last time</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md">
                    <h5>Resources</h5>
                    <ul class="list-unstyled text-small">
                        <li class="mb-1"><a class="link-secondary text-decoration-none" href="#">Resource</a></li>
                        <li class="mb-1"><a class="link-secondary text-decoration-none" href="#">Resource name</a></li>
                        <li class="mb-1"><a class="link-secondary text-decoration-none" href="#">Another resource</a></li>
                        <li class="mb-1"><a class="link-secondary text-decoration-none" href="#">Final resource</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md">
                    <h5>About</h5>
                    <ul class="list-unstyled text-small">
                        <li class="mb-1"><a class="link-secondary text-decoration-none" href="#">Team</a></li>
                        <li class="mb-1"><a class="link-secondary text-decoration-none" href="#">Locations</a></li>
                        <li class="mb-1"><a class="link-secondary text-decoration-none" href="#">Privacy</a></li>
                        <li class="mb-1"><a class="link-secondary text-decoration-none" href="#">Terms</a></li>
                    </ul>
                </div>
            </div>
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>

</html>