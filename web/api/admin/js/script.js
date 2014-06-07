(function () {
  // Qa controller
  var AdminApp = angular.module("AdminApp", []);
  
  AdminApp.controller("QAController", ["$scope", "$http" ,function ($scope, $http) {
    $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
    $http.defaults.transformRequest = function(data){
        if (data === undefined) {
            return data;
        }
        return $.param(data);
    };
    
    $scope.form_class = "";
    // 打开 Q&A popup 
    $scope.openQaFormPopup = function (event) {
      var self = angular.element(event.target);
      var qaid = self.attr("data-id");
      if (typeof qaid != "undefined") {
        // TODO:: 修改
        var response = $http.get("/api/question/index?qaid=" + qaid);
        $scope.question = {};
        response.success(function (data, status, headers, config) {
          if (data["status"] == 0) {
              $scope.question = data["data"];
          }
        });
        response.error(function () {
          alert("未知错误");
        });
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
    
    $scope.qaFormSubmit = function () {
      var formNg = $scope.qaform;
      if (!formNg.$valid) {
        return;
      }
      else {
        var question = angular.fromJson($scope.question);
        var response = $http.post("/api/question/add", question);
        response.success(function (data, status) {
          window.location.reload();
        });
      }
    };
    
    // 删除Qa
    $scope.deleteQa = function (event) {
      var el = angular.element(event.target);
      var qaid = el.attr("data-id");
      var ret = confirm("确认要删除这条数据？");
      if (ret) {
        var response = $http.post("/api/question/delete", {qaid: qaid});
        response.success(function (data, status) {
          window.location.reload();
        });
      }
    };
    
    // 初始化 Datatable plugin
    angular.element(document).ready(function () {
      angular.element("#qa-table").dataTable();
    });
  }]);

  // E-mails Controller
  AdminApp.controller("EmailController", ["$scope", function ($scope) {
      // 初始化 Datatable plugin
      $scope.initDataTablePlugin = function () {
        angular.element("#email-table").dataTable();
      };
  }]);

  // Team Controller
  AdminApp.controller("TeamController", ["$scope", "$http", function ($scope, $http) {
      // 初始化
      angular.element(document).ready(function () {
        angular.element("#team-table").dataTable();
      });
  }]);

  // Fuel Controller
  AdminApp.controller("FuelController", ["$scope", "$http", function ($scope, $http) {
      // 初始化
      angular.element(document).ready(function () {
        angular.element("#fuel-table").dataTable();
      });
      
      $scope.openFuelFormPopup = function () {
        
      }
  }]);
})();