<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Raabiha Olshop' }}</title>
    <style>
        body {
            background-color: #FAF7F0;
            font-family: 'Hanken Grotesk', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #222523;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border: 1px solid #e5e1d8;
            border-radius: 0px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }
        .header {
            background-color: #222523;
            padding: 30px 40px;
            text-align: center;
            border-bottom: 3px solid #0b4e26;
        }
        .header h1 {
            color: #FAF7F0;
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 24px;
            letter-spacing: 0.2em;
            margin: 0;
            text-transform: uppercase;
        }
        .content {
            padding: 40px;
        }
        .greeting {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 20px;
            font-weight: bold;
            color: #222523;
            margin-bottom: 20px;
        }
        .message {
            font-size: 14px;
            line-height: 1.6;
            color: #615e57;
            margin-bottom: 30px;
        }
        .button-wrapper {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background-color: #0b4e26;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            border-radius: 0px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            font-size: 13px;
        }
        .details-table th {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #615e57;
            background-color: #f2efe8;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e1d8;
        }
        .details-table td {
            padding: 12px;
            border-bottom: 1px solid #f2efe8;
            color: #222523;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }
        .footer {
            background-color: #f2efe8;
            padding: 30px 40px;
            text-align: center;
            font-size: 11px;
            color: #615e57;
            border-top: 1px solid #e5e1d8;
            line-height: 1.5;
        }
        .footer a {
            color: #0b4e26;
            text-decoration: none;
        }
        .bold {
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .price {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $siteName ?? 'RAABIHA' }}</h1>
        </div>
        <div class="content">
            @if(isset($greeting))
                <div class="greeting">{{ $greeting }}</div>
            @endif
            
            <div class="message">
                {!! $messageBody ?? '' !!}
            </div>

            @yield('content_details')

            @if(isset($actionUrl) && isset($actionText))
                <div class="button-wrapper">
                    <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
                </div>
            @endif
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $siteName ?? 'Raabiha' }}. All rights reserved.<br>
            Jika Anda memiliki pertanyaan, hubungi kami melalui <a href="{{ url('/contact') }}">halaman kontak</a> kami.
        </div>
    </div>
</body>
</html>
