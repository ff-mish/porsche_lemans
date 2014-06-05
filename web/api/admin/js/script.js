(function () {
  // Qa controller
  var AdminApp = angular.module("AdminApp", []);
  
  AdminApp.controller("QAController", ["$scope", function ($scope) {
      $scope.form_class = "";
    // 打开 Q&A popup 
    $scope.openQaFormPopup = function (qaId) {
      if (typeof qaId != "undefined") {
        // TODO:: 修改
      }
      if ($scope.form_class == "showme") {
        $scope.form_class = "";
      }
      else {
        $scope.form_class = "showme popup";
      }
    };
    
    $scope.closeQaFormPopup = function () {
      $scope.form_class = "";
    };
    
    // 初始化 Datatable plugin
    angular.element(document).ready(function () {
      angular.element("#qa-table").dataTable();
    });
  }]);

  AdminApp.controller("EmailController", ["$scope", function ($scope) {
      // 初始化 Datatable plugin
      $scope.initDataTablePlugin = function () {
        angular.element("#email-table").dataTable();
      };
  }]);
})();