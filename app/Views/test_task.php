<!DOCTYPE html>
<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <title>PHP Teszt Feladat</title>
    </head>
    <body>
        <?php if ($errorMessages !== null && count($errorMessages) > 0) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo implode('<br/>', $errorMessages) ?>
            </div>
        <?php endif; ?>
        <h1>Teszt Feladat</h1>
        <p>Tabajdi Szabolcs</p>
        <h3>Felhasználók</h3>
        <ul>
            <?php foreach ($users as $userData) { ?>
                <li><?php printf('%s %s (%s)', $userData['first_name'], $userData['last_name'], $userData['groupName'])  ?></li>
            <?php } ?>
        </ul>
        <h1>Add User</h1>
        <form action="<?php echo base_url('home/saveUser') ?>" method="post">
            <label for="firstname">Firstname</label>
            <input type="text" id="firstname" name="firstname">
            <label for="lastname">Lastname</label>
            <input type="text" id="lastname" name="lastname">
            <button type="submit">Save</button>
        </form>
        <h1>Add Group</h1>
        <form role="form" action="<?php echo base_url('home/saveGroup') ?>" method="post">
            <label for="group_name">Group Name</label>
            <input type="text" id="group_name" name="group_name">
            <label for="lastname">Group Code</label>
            <input type="text" id="group_code" name="group_code" maxlength="4">
            <button type="submit">Save</button>
        </form>
        <p>Véletlenszerű felhasználó: <?php echo $randomUser !== null ? $randomUser['first_name'] . ' ' .  $randomUser['last_name'] : '' ?></p>
        <p>A felhasználó e-mail címe: <?php echo $randomUser !== null ? $randomUser['email'] : '' ?></p>
        <p>Véletlenszerű csoport: <?php echo $randomGroup !== null ? $randomGroup['name'] : '' ?></p>
        <a href="http://lpsolutions.hu">L&P Solutions</a>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>