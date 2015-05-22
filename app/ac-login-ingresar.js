(function () {
    'use strict';

    var scripts = document.getElementsByTagName("script");
    var currentScriptPath = scripts[scripts.length-1].src;
    //console.log(currentScriptPath);

    angular.module('acLoginCarritoIngresar', ['ngRoute', 'login.login'])
        .directive('acLoginCarritoIngresar', AcLoginCarritoIngresar);


    AcLoginCarritoIngresar.$inject = ['$location', '$route', 'LoginService'];

    function AcLoginCarritoIngresar($location, $route, LoginService) {
        return {
            restrict: 'E',
            scope: {
                tipo: '='
            },
            templateUrl: currentScriptPath.replace('.js', '.html'),
            controller: function ($scope, $compile, $http) {

                var vm = this;
                vm.nombre = '';
                vm.apellido = '';
                vm.mail = '';
                vm.password_repeat = '';
                vm.password = '';
                vm.control = $scope.tipo;

                vm.ingresar = ingresar;
                vm.crear = crear;
                vm.recuperar = recuperar;


                function ingresar(){
                    LoginService.login(vm.mail, vm.password, function(data){

                        console.log(data);
                    });
                }

                function crear(){

                    if(vm.password !== vm.password_repeat){
                        //toastr.error('Los password no coinciden.')
                    }
                    LoginService.create(vm.nombre, vm.apellido, vm.mail, vm.password, function(data){

                        console.log(data);
                    });
                }

                function recuperar(){

                }

            },

            controllerAs: 'acLoginCarritoIngresarCtrl'
        };
    }

})();