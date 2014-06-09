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
      var ret = confirm("Are you sure to delete this data？");
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
    $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
    $http.defaults.transformRequest = function(data){
        if (data === undefined) {
            return data;
        }
        return $.param(data);
    };
    
      $scope.form_class = "";
      // 初始化
      angular.element(document).ready(function () {
        angular.element("#fuel-table").dataTable();
      });
      
      // 打开Fuel 表单
      $scope.openFuelFormPopup = function (event) {
        var el = angular.element(event.target);
        if (el.attr("data-id")) {
          var mid = el.attr("data-id");
          var response = $http.get("/api/media?mid="+mid);
          response.success(function (data) {
            if (data["status"] == 0 ) {
              $scope.fuel = data["data"];
            }
            else {
              alert("Error happened");
              window.location.reload();
            }
          });
          
          response.error(function () {
            alert("Error happened");
            window.location.reload();
          });
        }
        
        
        // 显示隐藏表单
        if ($scope.form_class == "") {
          $scope.form_class = "showme";
        }
        else {
          $scope.form_class = "";
        }
      };
      
      // 关闭Fuel表单
      $scope.closeFuelFormPopup = function () {
        $scope.form_class = "";
      };
      
      // 添加Fuel
      $scope.addFuelFormSubmit = function () {
        var form = angular.element("form[name='addfuel']");
        var button = angular.element("input[type='button']", form);
        
        form.ajaxSubmit({
          beforeSubmit: function () {
            button.attr("disabled", "disabled");
          },
          success: function (data) {
            if (data["status"] == 0) {
              window.location.reload();
            }
            else {
              var msg = "";
              for (var k in data["ext"]) {
                msg += data["ext"][k] + " ";
              }
              button.removeAttr("disabled");
              alert(msg);
            }
          }
        });
      };
      
      // 删除Fuel
      $scope.deleteFuelConfirm  = function (event) {
        var mid = angular.element(event.target).attr("data-id");
        var ret = confirm("Are you sure to delete this data?");
        if (ret) {
          var response = $http.post("/api/media/delete", {mid: mid});
          response.success(function (data) {
            window.location.reload();
          });
          response.error(function () {
            window.location.reload();
          });
        }
        else {
          
        }
        
        return false;
      };
      
      
  }]);
})();