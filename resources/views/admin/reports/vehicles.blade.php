
<style>
    /* Basic reset */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Body styling */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        color: #333;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        text-align: center;
    }

    /* Container styling */
    .container {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        max-width: 600px;
        margin: 20px;
    }

    /* Message styling */
    .maintenance-message h1 {
        font-size: 2em;
        margin-bottom: 10px;
    }

    .maintenance-message p {
        font-size: 1.2em;
        margin-bottom: 10px;
    }

    /* Button styling */
    .btn {
        display: inline-block;
        font-size: 1em;
        color: #fff;
        background-color: #007bff;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        margin-top: 20px;
        transition: background-color 0.3s ease;
    }

    .btn:hover {
        background-color: #0056b3;
    }


    </style>

    <!DOCTYPE html>
    <html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mirëmbajtje</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <div class="container">
            <div class="maintenance-message">
                <h1>Përkohësisht jemi në zhvillim e sipër</h1>
                <p>Na vjen keq për shqetësimin. Po kryejmë disa përditësime dhe do të jemi përsëri në linjë së shpejti.</p>
                <p>Faleminderit për durimin tuaj!</p>
                <a href="/admin" class="btn">Kthehu në Pjesën Kryesore</a>
            </div>
        </div>
    </body>
    </html>

