<!--<h1>Hello Main/index!</h1>-->

<?php if (!empty($names)): ?>
    <?php foreach ($names as $name): ?>
        <?= $name->name ?> => <?= $name->surname ?> => <?= $name->email ?><br>
    <?php endforeach; ?>
<?php endif; ?>
