<?php
namespace frontend\controllers;

use Faker\Factory;
use shop\entities\User\User;
use shop\forms\manage\Shop\Product\PhotosForm;
use shop\forms\manage\Shop\Product\PhotosFormConsole;
use shop\forms\SubscribeForm;
use shop\helpers\JgrowlMessageHelper;
use shop\helpers\SendEmailHelper;
use shop\services\newsletter\Newsletter;
use shop\useCases\manage\Shop\ProductManageService;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\UploadedFile;

/**
 * Site controller
 */
class SiteController extends Controller
{
    private $newsletter;
    private $service;

    public function __construct($id, $module,
                               ProductManageService $service,
                               // Newsletter $newsletter,
                                array $config = [])
    {
        $this->service = $service;
        //$this->newsletter = $newsletter;
        parent::__construct($id, $module, $config);
    }

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
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['rally'],
                'rules' => [
                    [
                        'actions' =>['rally'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
                'denyCallback' => function($rule, $action) {
                    if ($action->id == 'rally' && \Yii::$app->user->isGuest){
                        \Yii::$app->getUser()->setReturnUrl(\Yii::$app->request->url);
                        \Yii::$app->session->setFlash('info', 'Для участия в розыгрыше, необходимо авторизоваться через ВКОНТАКТЕ');}
                    return $this->redirect('/login');
                },
            ],
        ];
    }

    public function actionAddTest(){
        $faker = Factory::create();
        $var = 26879;
        echo ( $var % 2 ) ? 'не четное' : 'четное';
    }

  /*  public function afterAction($action, $result)
    {
        if ($this->action->id == 'rally') {
            \Yii::$app->getUser()->setReturnUrl(\Yii::$app->request->url);
        }
        return parent::afterAction($action, $result);
    }*/

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        $this->layout = 'home';
        $subscribeForm = new SubscribeForm();
        if ($subscribeForm->load(\Yii::$app->request->post()) && $subscribeForm->validate()) {
            try {
                //$this->newsletter->subscribe($subscribeForm->email,'','');
                \Yii::$app->session->setFlash('info', 'Благодарим Вас за подписку на новости.');
                //return $this->goHome();
            } catch (\Exception $e) {
                \Yii::$app->errorHandler->logException($e);
                \Yii::$app->session->setFlash('warning', 'WARNING: Возможно ваш EMAIL уже есть в базе!');
            }
            return $this->refresh();
        }

        return $this->render('index', ['model'=>$subscribeForm]);
    }

    public function actionRally()
    {

        $user = User::findOne(\Yii::$app->user->id);
        if($user->networks) {
            $isGroupVK = false;
            $response = file_get_contents('https://api.vk.com/method/groups.isMember?group_id=132528657&user_id='.$user->networks[0]["identity"]);
            $response = Json::decode($response);
            if($response['response'])
                $isGroupVK = true;
            return $this->renderPartial('rally',['user'=>$user, 'isGroupVK'=>$isGroupVK]);
        }
        else {
            \Yii::$app->session->setFlash('error', 'Ваш аккаунт не связан с аккаунтом Вконтакте');
            return $this->redirect('/cabinet');
        }
    }

    public function actionSubscribe(SubscribeForm $subscribeForm)
    {

            try {
                //$this->newsletter->subscribe($subscribeForm->email,'','');
                \Yii::$app->session->setFlash('info', 'Благодарим Вас за подписку на новости.');
                //return $this->goHome();
            } catch (\Exception $e) {
                \Yii::$app->errorHandler->logException($e);
                \Yii::$app->session->setFlash('error', 'При подписке на новости произошла ошибка.');
            }
            return $this->goHome();

    }

    public function actionSubRally(){
        \Yii::$app->getUser()->setReturnUrl(\Yii::$app->request->url);
        return $this->render('sub-rally');
    }

    public function actionPaymentAndDelivery(){

        return $this->render('payment-and-delivery');
    }
/*
    public function actionMail()
    {
        //print_r(\Yii::$app->params['dkim']['privateKey']);
        $send = new SendEmailHelper(\Yii::$app->params['dkim']);
        echo $send->getPrivate();
        die();
        $this->layout = '@common/mail/layouts/html';
        return $this->render('@common/mail/auth/signup/confirm-html');
    } */

}
