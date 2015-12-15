<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title><?php print($docTitle); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/styles.css" />
    <?php print($head); ?>
</head>
<body>
    <div class="box-content box-padding">
        <?php print($content); ?>
    </div>
    <?php print(@$tail); ?>
</body>
</html>