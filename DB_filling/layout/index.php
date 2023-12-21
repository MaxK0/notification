<?php
    require_once '../php/helpers.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма занесения данных для ЭП</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- <======================== Форма ========================> -->
    <div class="form_container">        
        <form action="../php/actions/index.php" method="post">
            <div class="form__block">
                <label for="name">Имя</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    placeholder="Иван"
                    value="<?php echo old('name')?>"
                    <?php validationErrorAttr('name');?>
                />
                <?php if (hasValidationError('name')):?>
                    <small><?php validationErrorMessage('name')?></small>
                <?php endif?>

                <label for="surname">Фамилия</label>
                <input
                    type="text"
                    name="surname"
                    id="surname"
                    placeholder="Иванов"
                    value="<?php echo old('surname')?>"
                    <?php validationErrorAttr('surname');?>
                />
                <?php if (hasValidationError('surname')):?>
                    <small><?php validationErrorMessage('surname')?></small>
                <?php endif?>

                <label for="lastname">Отчество</label>   
                <input
                    type="text"
                    name="lastname"
                    id="lastname"
                    placeholder="Иванович"
                    value="<?php echo old('lastname')?>"
                    <?php validationErrorAttr('lastname');?>
                />
                <?php if (hasValidationError('lastname')):?>
                    <small><?php validationErrorMessage('lastname')?></small>
                <?php endif?>    

                <label for="release_date">Дата выпуска</label>
                <input
                    type="date"
                    name="release_date"
                    id="release_date"
                    value="<?php echo old('release_date')?>"
                    <?php validationErrorAttr('release_date');?>
                />
                <?php if (hasValidationError('release_date')):?>
                    <small><?php validationErrorMessage('release_date')?></small>
                <?php endif?>

                <label for="expiry_date">Дата истечения</label>
                <input
                    type="date"
                    name="expiry_date"
                    id="expiry_date"
                    value="<?php echo old('expiry_date')?>"
                    <?php validationErrorAttr('expiry_date');?>
                />
                <?php if (hasValidationError('expiry_date')):?>
                    <small><?php validationErrorMessage('expiry_date')?></small>
                <?php endif?>
            </div>

            <div class="form__block">
                <p>Заполните, как минимум, 1 поле:</p>
                <?php if (hasValidationError('contact')):?>
                        <small><?php validationErrorMessage('contact')?></small>
                <?php endif?>
                <label class="email" for="email">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    placeholder="ivan@gmail.com"
                    value="<?php echo old('email')?>"
                    <?php validationErrorAttr('email');?>
                />
                <?php if (hasValidationError('email')):?>
                    <small><?php validationErrorMessage('email')?></small>
                <?php endif?>

                <div class="relative_block">
                    <p class="at">@</p>
                    <label for="telegram_nick">Ник телеграма</label>
                    <input
                        type="text"
                        name="telegram_nick"
                        id="telegram_nick"
                        placeholder="ivan123"
                        value="<?php echo old('telegram_nick')?>"
                        <?php validationErrorAttr('telegram_nick');?>
                    />   
                    <?php if (hasValidationError('telegram_nick')):?>
                        <small><?php validationErrorMessage('telegram_nick')?></small>
                    <?php endif?>                 
                </div>
            </div>

            <div class="form__submit">
                <button type="submit" id="submit">Сохранить</button>                  
                <?php if (hasValidationError('submit')):?>
                    <small><?php validationErrorMessage('submit')?></small>
                <?php endif?>               
            </div>
        </form>
    </div>
    <!-- <======================== Конец формы ========================> -->
</body>
</html> 