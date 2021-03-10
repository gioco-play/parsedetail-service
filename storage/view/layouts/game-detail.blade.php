<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">

    @if ($parse_type != '')
    <link rel="stylesheet" type="text/css" href="/css/{{ $parse_type }}.css">
    @endif

    <title>Game Detail</title>

    <style type="text/css">
        h1{
            font-size: 2rem;
        }
        .result-word{
            font-size: 36px;
        }
        .text-blue{
            color: #409eff;
        }
        .text-red{
            color: #f56c6c;
        }
        /* 表格 */
        .table .thead-light th{
            color: #909399;
        }
        .table td{
            color: #606266;
        }
    </style>
</head>
<body>
@yield('game-detail')
</body>
</html>