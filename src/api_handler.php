<?php

class API_Handler {

	private $function_map;

	public function __construct() {
		$this->loadFunctionMap();
	}

	public function execCommand($requestController, $requestAction, $requestParams) {

		// get the actual function name (if necessary) and the class it belongs to.
		$returnArray = $this->getCommand($requestController);

		// if we don't get a function back, then raise the error
		if ($returnArray['success'] == FALSE) {
			return $returnArray;
		}

		$class = $returnArray['dataArray']['class'];
		$controllerName = $returnArray['dataArray']['controller_name'];
	
		// Execute User Profile Commands
		$cObjectClass = new $class();
		$returnArray = $cObjectClass->$controllerName($requestAction, $requestParams);

		return $returnArray;

	}

	private function getCommand($requestController) {

		if (isset($this->function_map[$requestController])) {
			$dataArray['class'] = $this->function_map[$requestController]['class'];
			$dataArray['controller_name'] = $this->function_map[$requestController]['controller_name'];
			$returnArray = App_Response::getResponse('200');
			$returnArray['dataArray'] = $dataArray;
		} else {
			$returnArray = App_Response::getResponse('405');
		}

		return $returnArray;

	}


	// -------------------------------------------------------------------
	//                       API dessin
	// -------------------------------------------------------------------

	public function DrawingController($action, $params) {

		$drawingService = new DrawingService();
		$arrayReturn = $drawingService->$action($params);

		return $arrayReturn;
	}


	// -------------------------------------------------------------------
	//                       Fin API dessin
	// -------------------------------------------------------------------

	
	private function loadFunctionMap() {

		// load up all public facing functions
		$this->function_map = [
			'getToken' => ['class' => 'API_Handler', 'controller_name' => 'getToken'],
			'getHello' => ['class' => 'API_Handler', 'controller_name' => 'getHello'],
			'getArticles' => ['class' => 'API_Handler', 'controller_name' => 'getArticles'],
			

			// api blog - dessin
			'DrawingController' => ['class' => 'API_Handler', 'controller_name' => 'DrawingController'],
		];

	}
}