<?php

declare(strict_types=1);

return [
    'nav' => [
        'label' => 'Hoofdnavigatie',
        'home' => 'Home',
        'scanner' => 'Scannen',
        'meals' => 'Maaltijden',
        'account' => 'Account',
    ],
    'days' => [
        'Monday' => 'Maandag',
        'Tuesday' => 'Dinsdag',
        'Wednesday' => 'Woensdag',
        'Thursday' => 'Donderdag',
        'Friday' => 'Vrijdag',
        'Saturday' => 'Zaterdag',
        'Sunday' => 'Zondag',
    ],
    'common' => [
        'cancel' => 'Annuleren',
        'delete' => 'Verwijderen',
        'edit' => 'Bewerken',
        'not_available' => 'Niet beschikbaar',
        'unknown' => 'Onbekend',
    ],
    'meal_form' => [
        'error' => 'Controleer de ingevulde gegevens en voeg minimaal één ingrediënt toe.',
        'name' => 'Naam van de maaltijd',
        'name_placeholder' => 'Bijvoorbeeld Ontbijt',
        'servings' => 'Aantal porties',
        'prep_day' => 'Voorbereidingsdag',
        'ingredients' => 'Ingrediënten',
        'add_ingredient' => 'Ingrediënt toevoegen',
        'ingredient' => 'Ingrediënt',
        'ingredient_placeholder' => 'Bijvoorbeeld tacoschelpen',
        'quantity' => 'Hoeveelheid',
        'unit' => 'Eenheid',
    ],
    'meals' => [
        'title' => 'Maaltijden',
        'new' => 'Nieuwe maaltijd',
        'create' => 'Maaltijd aanmaken',
        'edit' => 'Maaltijd bewerken',
        'save' => 'Wijzigingen opslaan',
        'prepare_on' => 'Voorbereiden op :day',
        'summary' => ':servings porties · :ingredients ingrediënten',
        'delete_confirm' => 'Deze maaltijd verwijderen?',
        'empty' => 'Nog geen maaltijden',
        'empty_action' => 'Maaltijd maken',
    ],
    'scanner' => [
        'title' => 'Product scannen',
        'prepare_on' => 'Voorbereiden op :day',
        'drawer_label' => 'Scaninformatie',
        'drawer_handle' => 'Scanpaneel verslepen',
        'scan_prompt' => 'Plaats de barcode binnen het kader.',
        'scan_success' => 'Barcode succesvol gescand!',
    ],
];
