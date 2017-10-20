yii\web\BadRequestHttpException：状态码 400。
yii\web\ConflictHttpException：状态码 409。
yii\web\ForbiddenHttpException：状态码 403。
yii\web\GoneHttpException：状态码 410。
yii\web\MethodNotAllowedHttpException：状态码 405。
yii\web\NotAcceptableHttpException：状态码 406。
yii\web\NotFoundHttpException：状态码 404。
yii\web\ServerErrorHttpException：状态码 500。
yii\web\TooManyRequestsHttpException：状态码 429。
yii\web\UnauthorizedHttpException：状态码 401。
yii\web\UnsupportedMediaTypeHttpException：状态码 415。
如果想抛出的异常不在如上列表中，可创建一个yii\web\HttpException异常， 带上状态码抛出，如下：

throw new \yii\web\HttpException(402);
