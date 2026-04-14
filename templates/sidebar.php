<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SMnt - Online</title>

    <!-- Custom fonts for this template-->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Custom styles for this template-->
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <!-- DATA TABLEAS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-danger sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../pages/home.php">
                <div class="sidebar-brand-icon">
                    <i class="bi bi-tools"></i>
                </div>
                <div class="sidebar-brand-text mx-3">SMnt Online</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="../pages/dashboard.php">
                    <i class="fas fa-fw fa-home"></i>
                    <span>Página inicial</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Funcionalidades
            </div>

            <!-- Nav Item - Viaturas - Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseViatura"
                    aria-expanded="true" aria-controls="collapseViatura">
                    <i class="fas fa-fw fa-truck"></i>
                    <span>Viaturas</span>
                </a>
                <div id="collapseViatura" class="collapse" aria-labelledby="headingOne" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Funções para viaturas:</h6>
                        <a class="collapse-item" href="../pages/cadastro_viatura.php">Cadastrar</a>
                        <a class="collapse-item" href="../pages/pesquisar_viatura.php">Pesquisar</a>
                        <h6 class="collapse-header">Funções para acessórios:</h6>
                        <a class="collapse-item" href="../pages/gestao_acessorios.php">Cadastrar</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Utilities Collapse Menu -->
            <li class="nav-item ">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseManutencao"
                    aria-expanded="true" aria-controls="collapseManutencao">
                    <i class="fas fa-fw fa-wrench"></i>
                    <span>Manutenção</span>
                </a>
                <div id="collapseManutencao" class="collapse" aria-labelledby="headingManutencao"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Funções de manutenção:</h6>
                        <a class="collapse-item" href="../pages/pesquisar_manutencoes.php"><i class="fas fa-search me-2"></i>&nbsp;Pesquisar</a>
                        <a class="collapse-item" href="../pages/registrar_manutencao.php"><i class="fas fa-plus-circle me-2"></i>&nbsp;Registrar</a>
                        <h6 class="collapse-header">Funções de avarias:</h6>
                        <a class="collapse-item" href="../pages/pesquisar_avaria.php"><i class="fas fa-search me-2"></i>&nbsp;Pesquisar</a>
                        <a class="collapse-item" href="../pages/registrar_avaria.php"><i class="fas fa-plus-circle me-2"></i>&nbsp;Registrar</a>
                        <!--<a class="collapse-item" href="utilities-animation.html">Animations</a>
                        <a class="collapse-item" href="utilities-other.html">Other</a> -->
                    </div>
                </div>
            </li>

            <!-- Nav Item - Utilities Collapse Menu -->
            <li class="nav-item ">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseRevisao"
                    aria-expanded="true" aria-controls="collapseRevisao">
                    <i class="fas fa-fw fa-tasks"></i>
                    <span>Revisões</span>
                </a>
                <div id="collapseRevisao" class="collapse" aria-labelledby="headingRevisao"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Funções de Revisão:</h6>
                        <a class="collapse-item" href="../pages/pesquisar_revisao.php">Pesquisar</a>
                        <a class="collapse-item" href="../pages/cadastro_revisao.php">Cadastrar Revisão</a>
                        <!-- <a class="collapse-item" href="utilities-border.html">Borders</a>
                        <a class="collapse-item" href="utilities-animation.html">Animations</a>
                        <a class="collapse-item" href="utilities-other.html">Other</a> -->
                    </div>
                </div>
            </li>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePlanos"
                    aria-expanded="true" aria-controls="collapsePlanos">
                    <i class="fas fa-fw fa-business-time"></i>
                    <span>Planos de Manutenção</span>
                </a>
                <div id="collapsePlanos" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Opções de planos:</h6>
                        <a class="collapse-item" href="../pages/cadastro_plano_manutencao.php">Cadastrar Plano</a>
                        <a class="collapse-item" href="../pages/cadastro_itens_preventiva.php">Cadastrar Itens</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Condutores</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Opções de condutor:</h6>
                        <a class="collapse-item" href="../pages/cadastrar_condutor.php">Cadastrar</a>
                        <a class="collapse-item" href="../pages/pesquisar_condutor.php">Pesquisar</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMovimento"
                    aria-expanded="true" aria-controls="collapseMovimento">
                    <i class="fas fa-fw fa-random"></i>
                    <span>Movimentação</span>
                </a>
                <div id="collapseMovimento" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Opções de movimentação:</h6>
                        <a class="collapse-item" href="../pages/transferir_viatura.php">Transferir Viatura</a>
                        <a class="collapse-item" href="../pages/confirmar_recebimento.php">Confirmar Recebimento</a>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Extras
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseOficina"
                    aria-expanded="true" aria-controls="collapseOficina">
                    <i class="fas fa-fw fa-store"></i>
                    <span>Oficinas</span>
                </a>
                <div id="collapseOficina" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Opções para oficinas:</h6>
                        <a class="collapse-item" href="../pages/cadastro_oficina.php">Cadastrar</a>
                        <!-- <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Other Pages:</h6>
                        <a class="collapse-item" href="404.html">404 Page</a>
                        <a class="collapse-item" href="blank.html">Blank Page</a> -->
                    </div>
                </div>
            </li>

            <!-- Nav Item - Charts -->
            <!--
            <li class="nav-item">
                <a class="nav-link" href="charts.html">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Charts</span></a>
                </li>
            -->

            <!-- Nav Item - Tables -->
            <li class="nav-item">
                <a class="nav-link" href="../pages/mensagens.php">
                    <i class="fas fa-fw fa-envelope"></i>
                    <span>Mensagens</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../pages/mapa_viaturas.php">
                    <i class="fas fa-fw fa-map"></i>
                    <span>Mapa de viaturas</span></a>
            </li>

            <!-- Seção Administrativa (Escondida por padrão) -->
            <div id="secao-admin-sidebar" class="d-none">
                <hr class="sidebar-divider">
                <div class="sidebar-heading">
                    Configurações Master
                </div>
                <li class="nav-item">
                    <a class="nav-link" href="pages/gestao_usuarios.php">
                        <i class="fas fa-fw fa-users-cog"></i>
                        <span>Gestão de Usuários</span>
                    </a>
                </li>
            </div>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

            <!-- Sidebar Message -->
            <!-- <div class="sidebar-card d-none d-lg-flex">
                <img class="sidebar-card-illustration mb-2" src="img/undraw_rocket.svg" alt="...">
                <p class="text-center mb-2"><strong>SB Admin Pro</strong> is packed with premium features, components, and more!</p>
                <a class="btn btn-success btn-sm" href="https://startbootstrap.com/theme/sb-admin-pro">Upgrade to Pro!</a>
            </div> -->

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Search -->
                    <!-- Topbar Search -->
                    <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search" id="globalSearchForm" autocomplete="off">
                        <div class="input-group" style="position: relative;"> <!-- position relative é importante! -->
                            <input type="text" class="form-control bg-light border-0 small" id="globalSearchInput"
                                placeholder="Buscar viatura ou condutor..." aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-danger" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>

                            <!-- CONTAINER PARA OS RESULTADOS DA BUSCA -->
                            <div id="globalSearchResults" class="dropdown-menu shadow w-100 mt-1" style="display: none; position: absolute; top: 100%; z-index: 1050;">
                                <!-- JS preencherá aqui -->
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- O contador -->
                                <span class="badge badge-danger badge-counter d-none" id="notificacao-contador">0</span>
                            </a>
                            <!-- Onde as notas vão aparecer -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">Centro de Alertas</h6>
                                <div id="container-notificacoes-itens">
                                    <!-- JS preenche aqui -->
                                </div>
                                <a class="dropdown-item text-center small text-gray-500" href="pages/notificacoes.php">Ver todas as notificações</a>
                            </div>
                        </li>

                        <!-- Nav Item - Messages -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-envelope fa-fw"></i>
                                <!-- Counter - Messages -->
                                <span class="badge badge-danger badge-counter d-none" id="msg-contador">0</span>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="messagesDropdown">
                                <h6 class="dropdown-header">
                                    Message Center
                                </h6>
                                <!-- Onde as mensagens serão injetadas -->
                                <div id="container-mensagens-itens">
                                    <p class="small text-center p-3 text-muted">Carregando...</p>
                                </div>

                                <a class="dropdown-item text-center small text-gray-500" href="pages/mensagens.php">Ler todas as mensagens</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <!-- Adicionamos o ID user-name-display -->
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small" id="user-name-display">Carregando...</span>
                                <img class="img-profile rounded-circle" id="user-img-topbar" src="assets/uploads/perfil/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="/pages/perfil.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Perfil
                                </a>
                                <a class="dropdown-item" href="/pages/configuracoes.php">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Alterar senha
                                </a>
                                <a class="dropdown-item" href="/pages/logs_atividades.php">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Histórico (log)
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Sair
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->