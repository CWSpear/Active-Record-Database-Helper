 <!DOCTYPE html>
<html>
<head>
    <title>CamDB Tests</title>

    <style type="text/css">
        body {
            background: url('img/wood.jpg');
        }

        #wrapper {
            max-width:1240px;
            margin:0 auto;
        }

        .result {
            border:1px solid rgba(0, 0, 0, 0.3);
            border-radius:4px;
            padding:20px;
            margin-bottom:20px;

            background-color:rgba(0, 0, 0, 0.1)
        }

        .input, .expected, .actual, .passfail {
            position:relative;
            padding:6px;
            margin-bottom:10px;
        }

        .output {
            margin-left:100px;
        }

        .label, .icon {
            position:absolute;
            left:0;
            top:0;
            padding:6px;
            border-radius:4px;
            box-shadow:inset 0 0 5px rgba(0, 0, 0, 0.3);
            width:80px;
            background-color:rgba(0, 0, 0, 0.1)
        }

        .fail .icon {
            background-color:rgba(128, 0, 0, 0.5);
        }

        .pass .icon {
            background-color:rgba(0, 128, 0, 0.5);
        }
    </style>
</head>
<body>
    <div id="wrapper">
    <?php foreach($results as $result): extract($result) ?>
        <div class="result">
            <div class="input">
                <div class="label">Input</div> 
                <div class="output"><?php echo $input ?></div>
            </div>
            <div class="expected">
                <div class="label">Expected</div> 
                <div class="output"><?php echo $expected ?></div>
            </div>
            <div class="actual">
                <div class="label">Actual</div> 
                <div class="output"><?php echo $actual ?></div>
            </div>
            <div class="passfail <?php echo $class ?>">
                <div class="icon"><?php echo $class ?></div>
                <div class="output"><?php echo $class == 'fail' ? 'Failed' : 'Passed' ?></div>
            </div>
        </div>
    <?php endforeach ?>
    </div>
</body>
</html> 