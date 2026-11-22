<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        html, body { width: 297mm; height: 210mm; margin: 0; padding: 0; }
        @include('pages.user.rtl.certificate-styles')
    </style>
</head>
<body>
    @include('pages.user.rtl._certificate-content', [
        'backgroundSrc' => 'data:image/png;base64,'.$background,
        'logoSrc' => 'data:image/png;base64,'.$logo,
        'topLogoLeftSrc' => 'data:image/png;base64,'.$topLogoLeft,
        'topLogoRightSrc' => 'data:image/png;base64,'.$topLogoRight,
        'qrImageSrc' => $qrImage ?? '',
    ])
    @include('pages.user.rtl._certificate-materials-content', [
        'materialsBackgroundSrc' => 'data:image/png;base64,'.$materialsBackground,
    ])
</body>
</html>
