<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Offline - Site Maintenance</title>
    <style type="text/css">
        body {
            text-align: center;
            padding: 150px;
            font: 20px Helvetica, sans-serif;
            color: #333;
            background-color: #f9f9f9;
        }

        h1 {
            font-size: 50px;
        }

        article {
            display: block;
            text-align: left;
            width: 650px;
            margin: 0 auto;
        }

        a,
        button {
            color: #fff;
            background-color: #dc8100;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
        }

        a:hover,
        button:hover {
            background-color: #a35e00;
        }

        .actions {
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <article>
        <h1>We&rsquo;ll be back soon!</h1>
        <div>
            <p>Sorry for the inconvenience but we&rsquo;re currently offline or performing some maintenance.
                We&rsquo;ll be back online shortly!</p>
            <p>&mdash; The Team RSCH</p>

            <div class="actions">
                <button onclick="location.reload()">Refresh</button>
                <a href="/">Go to Homepage</a>
            </div>
        </div>
    </article>
</body>

</html>
