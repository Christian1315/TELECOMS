<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>FRIK SMS DEVELOPPEUR</title>
    <link rel="shortcut icon" href="logo.jpeg" type="image/x-icon">
    <link rel="stylesheet" href="bootstrap.css">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous"> -->
    <link rel="stylesheet" href="style.css">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <style>
        p {
            font-size: 25px !important;
        }
    </style>
</head>

<body class="antialiased">
    <div class="container-fluid shadow-lg bg-light header">
        <div class="row">
            <div class="col-md-12">
                <nav class="navbar navbar-expand-lg">
                    <div class="container-fluid">
                        <a class="shadow-lg rounded navbar-brand text-white bg-dark px-3" href="/"> <strong>FRIK SMS</strong></a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                <li class="nav-item">
                                    <a href="#" class="nav-link active" aria-current="page" href="#">Site Officiel</a>
                                </li>
                                <!-- <li class="nav-item">
                                    <a class="nav-link" href="/documentation">Api Documentation</a>
                                </li> -->
                            </ul>
                            <form class="d-flex" role="search">
                                <img src="logo.jpeg" width="300" class="shadow-lg p-3 bg-body rounded" alt="" srcset="">
                            </form>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="container content">
                    <div class="row">
                        <div class="col-md-12 text-center sur l'accueil">
                            <a href="/" class="return">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z" />
                                </svg>
                                <strong>Retour</strong>
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <p class="text-dark">FRIK SMS innove afin de garantir à ses clients
                                une visibilité optimale DE MARKETING par messagérie</p>
                        </div>
                    </div>

                    <!-- ################ TOUTES LES ROUTES RELATIVES AUX USERS ############### -->
                    <div class="bg-dark text-center mb-5">
                        <h1 class="text-white">COMMENT FONCTIONNE FRIK_SMS POUR LES DÉVELOPPEURS !?
                        </h1>
                    </div>
                    <div class="row" id="documenation">
                        <div class="col-md-12">

                            <!-- ETAPE 1 -->

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="text-center">
                                        <button disabled class="btn documentation ">##============ Etape 1: CREATION DE COMPTE ==========##</button>
                                    </div>
                                    <p class="text-dark text-center">
                                    <p class="">1 - Rendez-vous sur FRIK SMS via ce lien: <a href="https://manager.telecoms.digital/auth/login" target="_blank" rel="noopener noreferrer">https://manager.telecoms.digital/auth/login</a> </p>
                                    <br>
                                    <p class="">2 - Créer votre compte si vous n’en dispose pas un!</p>
                                    </p>
                                </div>
                            </div>

                            <!-- ETAPE 2 -->
                            <div class="row mt-5">
                                <div class="col-md-12">
                                    <div class="text-center">
                                        <button disabled class="btn documentation ">##============ Etape 2: Générer une clé API ==========##</button>
                                    </div>
                                    <p class="text-dark text-center">
                                    <p class="">1 - Connectez-vous à votre compte</p>
                                    <br>
                                    <p class="">2 - Rendez-vous dans le panel des Développeurs!</p>

                                    <img src="../../sms/dev_key_generate.png" width="700px" srcset="">
                                    </p>
                                </div>
                            </div>

                            <!-- ETAPE 3 -->
                            <div class="row mt-5">
                                <div class="col-md-12">
                                    <div class="text-center">
                                        <button disabled class="btn documentation ">##============ Etape 3: ENVOIE D'SMS ==========##</button>
                                    </div>
                                    <p class="text-dark text-center">
                                    <p class="">1 - Récupérer votre ID d’utilisateur dans le panel Développeurs & votre Clé API </p>
                                    <img src="../../sms/dev_key_generate.png" width="700px" srcset="">

                                    <br><br>
                                    <p class="">2 - Faites une requête POST via cet URL <a href="https://api.telecoms.digital/api/v1/developer/sms/send" target="_blank" rel="noopener noreferrer">https://api.telecoms.digital/api/v1/developer/sms/send</a>,puis ensuite préciser dans le headers de la requête votre ID d’utilisateur et votre Clé API: </p>
                                    <br>
                                    <img src="../../sms/send_sms_dev.png" width="700px" srcset="">

                                    <br><br>
                                    <p class="">3 - Préciser dans le body de la requête, le phone(le numéro du destinataire), le message(le contenu de votre message), et enfin l’expéditeur(le nom à afficher dans l'entête de votre message. Et rassurez-vous que cet expéditeur est validé sur la plateforme)</p>
                                    <br>
                                    <img src="../../sms/send_sms_dev_body.png" width="700px" srcset="">

                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mt-5"> <strong class="bg-dark p-1 text-white ">URL ::</strong> <a href="https://api.telecoms.digital/api/v1/developer/sms/send" target="_blank" rel="noopener noreferrer">https://api.telecoms.digital/api/v1/developer/sms/send</a> </h5>

                                    <h5 class="mt-5"> <strong class="bg-dark p-1 text-white ">DATA ::</strong></h5>
                                    <p class="">
                                    <ul>
                                        <li>data =
                                            <ul>
                                                <li>{</li>
                                                "phone":'22965645432', <br>
                                                "message": ""Salut! C'est moi",<br>
                                                "expediteur": "mon-expediteur"
                                                <li>}</li>
                                            </ul>
                                        </li>
                                    </ul>
                                    <ul>
                                        <li>header =
                                            <ul>
                                                <li>{</li>
                                                "api-key": "J0eXAiOiJKV1QiLCJhbGci", <br>
                                                "id": 189, <br>
                                                <li>}</li>
                                            </ul>
                                        </li>
                                    </ul>
                                    </p>
                                    <h5 class="mt-5"> <strong class="bg-dark p-1 text-white ">EXEMPLE DE REQUEST::</strong> fetch.POST(url,option=header,json=data)</h5>
                                </div>
                            </div>

                            <!-- ETAPE 4 -->
                            <div class="row mt-5">
                                <div class="col-md-12">
                                    <div class="text-center">
                                        <button disabled class="btn documentation ">##============ Etape 4: GESTION DES SMS ==========##</button>
                                    </div>
                                    <p class="text-dark text-center">
                                    <p class="">1 - Récupération de tout vos sms </p>
                                    <small>Précisez dans le header de votre requête, votre <strong>Clé API</strong> et votre votre <strong>ID d'utilisateur</strong></small>
                                    <br><br>
                                    <img src="../../sms/get_all_sms.png" width="700px" srcset="">

                                    <br><br>
                                    <h5 class="mt-5"> <strong class="bg-dark p-1 text-white ">URL ::</strong> <a href="https://api.telecoms.digital/api/v1/developer/sms/all" target="_blank" rel="noopener noreferrer">https://api.telecoms.digital/api/v1/developer/sms/all</a> </h5>
                                    <p class="">
                                    <ul>
                                        <li>header =
                                            <ul>
                                                <li>{</li>
                                                "api-key": "J0eXAiOiJKV1QiLCJhbGci", <br>
                                                "id": 189, <br>
                                                <li>}</li>
                                            </ul>
                                        </li>
                                    </ul>
                                    </p>
                                    <h5 class="mt-5"> <strong class="bg-dark p-1 text-white ">EXEMPLE DE REQUEST::</strong> fetch.GET(url,option=header)</h5>
                                    </p>

                                    <p class="text-dark text-center">
                                    <p class="">2 - Récupération d'sms via son id </p>
                                    <small>Précisez dans le header de votre requête, votre <strong>Clé API</strong> et votre votre <strong>ID d'utilisateur</strong></small>
                                    <br><br>
                                    <img src="../../sms/get_sms.png" width="700px" srcset="">

                                    <br><br>
                                    <h5 class="mt-5"> <strong class="bg-dark p-1 text-white ">URL ::</strong> <a href="https://api.telecoms.digital/api/v1/developer/sms/{id_sms}/retrieve" target="_blank" rel="noopener noreferrer">https://api.telecoms.digital/api/v1/developer/sms/{id_sms}/retrieve</a> </h5>
                                    <p class="">
                                    <ul>
                                        <li>header =
                                            <ul>
                                                <li>{</li>
                                                "api-key": "J0eXAiOiJKV1QiLCJhbGci", <br>
                                                "id": 189, <br>
                                                <li>}</li>
                                            </ul>
                                        </li>
                                    </ul>
                                    </p>
                                    <h5 class="mt-5"> <strong class="bg-dark p-1 text-white ">EXEMPLE DE REQUEST::</strong> fetch.GET(url,option=header)</h5>
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid bg-light shadow-lg py-3 footer fixed-bottom d-none d-md-block d-md-lg">
            <div class="row">
                <div class="col-md-12">
                    <p class="text-dark text-center">© Copyright 2023 - Développé par HSMC</p>
                </div>
            </div>
        </div>

        <div class="container-fluid bg-light shadow-lg py-3 footer d-none d-sm-block">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="text-dark text-center">© Copyright 2023 - Développé par HSMC</h2>
                </div>
            </div>
        </div>
    </div>
</body>

</html>