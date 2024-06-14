<?php

use Dadata\DadataClient;

    class DaDataApiHelper
    {
        private string $token = '0f1c4ab86a8eff1d778dd1fdc00a07a6048bf23a';
        private DadataClient $dadata;

        public function __construct()
        {
            $this->dadata = new DadataClient($this->token, null);
        }

        public function getCity($ip)
        {
            return $this->dadata->iplocate($ip);
        } 
        
    }

    class PhoneNumsModel
    {
        private array $numsTable = [
            'Default' => '000-0000',
            'Краснодар' => '200-0600',
            'Москва' => '100-0600',
            'Воронеж' => '400-0600',
        ];

        public function getNum($city): string
        {
            return $this->numsTable[$city];
        }
    }
    class Controller
    {
        public function __construct(
            public readonly DaDataApiHelper $dadata,
            public readonly PhoneNumsModel $phoneNum
        ) {}

        public function actionIndex(): string
        {
            $ip = $_SERVER['REMOTE_ADDR'];
            $response = json_decode($this->dadata->getCity($ip));
            if (empty($response)) throw new Exception("Не получен ответ от сервиса DaData");
            if ($response['location'] === 'null') {
                return $this->phoneNum->getNum('Default');
            }
            return $this->phoneNum->getNum($response['location']['data']['city']);
            
        }
    }
    $controller = new Controller(new DaDataApiHelper(), new PhoneNumsModel());
    $digits = $controller->actionIndex();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div>
        <span>8-800-<?=$digits?></span>
    </div>
</body>
</html>