(function () {
    'use strict';

    //var destinationWebsite = "http://localhost/test-login/app/#/verify-login/";
    //var destinationWebsite = "http://192.185.67.199/~arielces/playground/redirect/#/verify-login/";
    var destinationWebsite = "http://192.185.67.199/~arielces/admin-hostel/site/#/verify-login/";

    var scripts = document.getElementsByTagName("script");
    var currentScriptPath = scripts[scripts.length-1].src;

    angular.module('login.login', ['ngRoute', 'ngCookies'])
    //angular.module('login.login', ['ngRoute', 'ngCookies'])
        .config(['$routeProvider', function ($routeProvider) {
            $routeProvider.when('/login/:action', {
                templateUrl: './login/login.html',
                controller: 'LoginCtrl'
            });
            $routeProvider.when('/login', {
                templateUrl: './login/login.html',
                controller: 'LoginCtrl'
            });
        }])
        .controller('LoginCtrl', LoginCtrl)
        .factory('LoginService', LoginService);

    //Injects
    LoginCtrl.$inject = ['LoginService', '$cookieStore', '$window', '$routeParams', '$location'];
    //LoginCtrl.$inject = ['LoginService', '$cookieStore', '$window', '$routeParams'];
    LoginService.$inject = ['$http', '$cookieStore', '$window'];


    //Implementations
    //function LoginCtrl(LoginService, $cookieStore, $window, $routeParams) {
    function LoginCtrl(LoginService, $cookieStore, $window, $routeParams, $location) {
        // Functions
        var vm = this;

        // Variables
	vm.username = '';
        vm.password = '';
        vm.changePwd = 0;
        
        // Functions declaration
        vm.login = login;
        vm.createUser = createUser;
        vm.recoveryPwd = recoveryPwd;
        vm.changePwd = changePwd;

        //Init
        checkLogged();

        //Implementations
        function login() {
            if(vm.username.trim().length > 0 && vm.password.trim().length > 0) {
                if(vm.password.trim().length >= 6 && vm.password.trim().length <= 25) {
                    LoginService.login(vm.username, vm.password, function (data){
                        //console.log(data);
                        if(data.response) {                            
                            var user = JSON.parse(data.user);
                            vm.changePwd = user.change_pwd;
                            if(user.change_pwd === 0) {
                                //$window.location.href = destinationWebsite;
                                LoginService.setLogged(user.user_name, user.usuario_id, user.rol_id, user.token);
                            }                                
                            else {
                                //toastr.warning('Por favor cambie la contraseña temporal');
                            }                        	
                        }
                        else {
                            //toastr.error('Usuario o contraseña invalido');
                        }
                    });
                }
                else {
                    //toastr.warning('La Password deben tener un mínimo de 6 caracteres y un maximo de 25');
                }
            }
            else {
                //toastr.error('Por favor ingrese un usuario y Password');
            }
        }

        function createUser() {
            $location.path("/crear");
        }

        function recoveryPwd() {
            $location.path("/recovery");
        }

        function changePwd() {
            $location.path("/changepwd");
        }

        function checkLogged() {
            if($routeParams !== undefined){
                if($routeParams.action == 'clear'){
                    $cookieStore.remove('appname.login.userLogged');
                }
            }
            var globals = $cookieStore.get('appname.login.userLogged');
            //console.log(globals);
            if (globals !== undefined &&
                globals.userid !== undefined &&
                globals.userid !== '') {
                LoginService.checkLastLogin(globals.userid, function (data) {
                    if (data) {
                        // Redirecciona a la aplicaci��n verdadera
                        //console.log('true');
                        $window.location.href = destinationWebsite + globals.verification + '/' + globals.userid;
                    }
                });
            }
        }
    }


    function LoginService($http, $cookieStore, $window) {
        //Variables
        var service = {};

        var url= currentScriptPath.replace('login.js','cliente.php');

        //Function declarations
        service.login = login;
        service.checkLogged = checkLogged;
        service.checkLastLogin = checkLastLogin;
        service.setLogged = setLogged;
        service.create = create;
        service.logout = logout;

        return service;

         //Functions
        function logout(){
            $cookieStore.remove('app.userlogged');
        }



        function login(mail, password, callback) {
            return $http.post(url,
                {'function': 'login', 'mail': mail, 'password': password})
                .success(function (data) {
                    if (data[0].nombre !== undefined) {
                        setLogged(data);
                    }                    
                    //console.log(data);
                    callback(data);
                })
                .error()
        }

        function checkLastLogin(userid, callback) {
            return $http.post('user.php',
                {function: 'checkLastLogin', 'userid': userid})
                .success(function (data) {
                    //console.log(data);
                    if(data !=='false'){

                        callback(data);
                    }
                })
                .error()
        }

        function setLogged(cliente) {
            var datos = {
                'cliente': cliente
            };
            $cookieStore.put('app.userlogged', datos);
        }


        function create(nombre, apellido, mail, password, fecha_nacimiento,
            telefono, direccion, callback){
            var user = {
                'nombre': nombre,
                'apellido': apellido,
                'mail': mail,
                'password': password,
                'fecha_nacimiento': fecha_nacimiento,
                'telefono': telefono,
                'direccion': direccion
            };
            return $http.post(url,
                {
                    'function': 'create',
                    'user': JSON.stringify(user)
                })
                .success(function(data){
                    callback(data);
                })
                .error(function(data){
                    console.log(data);
                });
        }
        function checkLogged() {
            var globals = $cookieStore.get('app.userlogged');

            if(globals!== undefined && globals.cliente !== undefined){
                return globals;
            }else{
                return false;
            }
        }

        function changePassword(cliente_id, pass_old, pass_new, callback){

            return $http.post(url,
                {
                function: 'changePassword',
                cliente_id : cliente_id,
                pass_old: pass_old,
                pass_new: pass_new
            })
                .success(function(data){
                    callback(data);
                })
                .error(function(data){
                    callback(data);
                })
        }
    }

})();