<?php
namespace Controllers;

class User extends Controller
{
    /**
     * Render list of Users without the email and password fields
     */
    public static function getAll()
    {
        self::_render('G001', self::_removeAttrs(
            \Models\User::getAll(), ['email', 'password']
        ));
    }
}