<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\User;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $email;
    public $password;
    public $rememberMe;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // email and password are both required
            [['email', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @param int $exp jwt expiration
     * @param int $refreshExp jwt refresh expiration
     * @return boolean whether the user is logged in successfully
     */
    public function login($exp, $refreshExp)
    {
        if ($this->validate()) {
            /** @var \app\components\JwtAuth $jwtAuth */
            $jwtAuth = Yii::$app->jwtAuth;

            $user = $this->getUser();
            $jwt = $jwtAuth->encode($user->toArray(), $exp);
            $refresh = $jwtAuth->encode($user->authKey, $refreshExp); // use authKey as the refresh for now
            return [ $user, $jwt, $refresh ];
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->email);
        }

        return $this->_user;
    }
}
