<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <title>FRIK-SMS</title>

    <style>
        * {
            font-family: Inter !important;
        }

        .header {
            background: #730208 !important;
            padding: 100px !important;
        }

        .title {
            font-size: 53px !important;
            font-weight: 400 !important;
            line-height: 64px !important;
            letter-spacing: 0.125em !important;
        }

        .bottom {
            background: #73020866 !important;
            padding-block: 10px !important;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important
        }

        #content {
            background: #fff !important;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
            margin-top: -100px !important;
            border-radius: 10px 10px 0px 0px !important;
        }

        #content .main {
            padding: 30px !important;
            /* display: none!important; */
        }

        .confirm-btn {
            background: #730208 !important;
            padding: 20px !important;
            border-radius: 5px !important;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
            color: #fff !important;
            font-size: 22px !important;
            font-weight: 400 !important;
            line-height: 27px !important;
            letter-spacing: 0em !important;
            text-align: center !important;
            margin-block: 50px !important;
        }

        p {
            font-size: 22px !important;
            font-weight: 400 !important;
            line-height: 27px !important;
            letter-spacing: 0em !important;
            margin-block: 30px !important;
        }

        a.link {
            color: #730208 !important;
            font-size: 18px !important;
            font-weight: 600 !important;
            line-height: 22px !important;
            letter-spacing: 0em !important;
            text-decoration: none !important;
        }

        .text-center {
            text-align: center !important;
        }

        .coordial {
            margin-block: 50px !important;
        }

        .container {
            background-color: #E8E8E8 !important;
            padding-top: 30px !important;
        }

        .bottom h3 {
            font-size: 18px !important;
            font-weight: 600 !important;
            line-height: 22px !important;
            letter-spacing: 0em !important;
        }

        .text-dark {
            color: #000 !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row header">
        </div>

        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8" id="content">
                <!-- main -->
                <div class="main">
                    <h1 class="text-center text-dark title">{{$details["subject"]}}</h1>
                    <p class="text-center">Nous sommes heureux de vous compter parmi les utilisateurs de frikSMS .</p>
                    <p class=""> {{$details["message"]}}</p>
                    <p class="text-center">
                        <a class="btn btn-lg confirm-btn">Visitez le site officiel</a>
                    </p>
                    <p class="">Si le bouton ne marche pas , copiez ce lien dans votre navigateur</p>

                    <a href="https//:inscritpion.friksms.com" class="link">Https//:inscritpion.friksms.com</a>

                    <p class="">Si vous avez une quelconque préoccupation , n’hésitez pas à répondre à cet email . Nous nous tiendrons à votre disposition</p>

                    <p class="coordial">
                        Cordialement, <br>
                        L’équipe de <strong>FRIKSMS</strong>
                    </p>
                </div>
                <!-- footer -->
                <div class="row shadow-lg py-3 text-center bottom">
                    <div class="col-md-12">
                        <h3 class="text-dark">© Copyright 2023 - Développé par HSMC</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>

    </div>
</body>

</html>