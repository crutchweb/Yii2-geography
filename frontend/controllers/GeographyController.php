<?php

namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use frontend\components\MainController;
use yii\widgets\ActiveForm;
use common\models\geography\Address;

/**
 * Site controller
 */
class GeographyController extends MainController
{

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        $this->attachBehavior('seo-site', [
            'class' => \gtd\modules\seo\behaviors\SeoBehavior::className(),
        ]);

        $this->attachBehavior('og-site', [
            'class' => \gtd\modules\seo\behaviors\OgBehavior::className(),
        ]);

        return $this->render('index');
    }

    public function actionAutocomplete()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            $term = Yii::$app->request->get('term');
            $term = \gtd\helpers\text\extended\LayoutSwitch::puntoSwitch($term, 'en', 'ru');

            $autocompleteModel = new \common\models\autocomplete\GegoraphyCity([
                'term' => $term,
                'site' => Yii::$app->id,
                'is_active' => Yii::$app->request->get("is_active")
            ]);
            $autocompleteModel->validate();

            return $autocompleteModel->getResult();
        } else {
            throw new \yii\web\NotFoundHttpException();
        }
    }

    public function actionGetPlacemark()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            $ajaxModel = new Address();
            $ajaxModel->validate();

            return $ajaxModel->getPlacemark();
        } else {
            throw new \yii\web\NotFoundHttpException();
        }
    }

    public function actionGetBranchData()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            $id = Yii::$app->request->get('id');
            $ajaxModel = new Address();

            return $ajaxModel->getBranchData($id);
        } else {
            throw new \yii\web\NotFoundHttpException();
        }
    }
}