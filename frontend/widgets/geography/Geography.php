<?

namespace frontend\widgets\geography;

use Yii;
use yii\bootstrap\Widget;
use \common\models\geography\Address;
use \common\models\geography\City;

class Geography extends Widget
{
    public $view = 'geography';
    public $client;

    public function run()
    {
        return $this->render($this->view, [
            "model_country" => new \common\models\kladr\Country(),
        ]);
    }
}