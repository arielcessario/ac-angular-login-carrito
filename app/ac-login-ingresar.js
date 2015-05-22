(function () {
    'use strict';

    var scripts = document.getElementsByTagName("script");
    var currentScriptPath = scripts[scripts.length-1].src;
    //console.log(currentScriptPath);

    angular.module('acLoginCarritoIngresar', ['ngRoute'])
        .directive('acLoginCarritoIngresar', AcLoginCarritoIngresar);


    AcLoginCarritoIngresar.$inject = ['$location', '$route'];

    function AcLoginCarritoIngresar($location, $route) {
        return {
            restrict: 'E',
            scope: {
                parametro: '='
            },
            templateUrl: currentScriptPath.replace('.js', '.html'),
            controller: function ($scope, $compile, $http) {

                var vm = this;

            },

            controllerAs: 'acLoginCarritoIngresarCtrl'
        };
    }

})();