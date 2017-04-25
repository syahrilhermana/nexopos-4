<script type="text/javascript">

    tendooApp.factory( 'sharedAlert', [ 'SweetAlert', function( SweetAlert ){
        return new function(){

            /**
             *  Alert Text
             *  @param string message
             *  @return void
            **/

            this.alert      =   function( message ) {
                return SweetAlert.swal( message );
            }

            /**
             *  Confirmation
             *  @param string message
             *  @param function callback
             *  @return void
            **/

            this.confirm    =   function( message, callback ) {
                return SweetAlert.swal({
                    title                : "Confirmez votre action",
                    text                 : message,
                    type                 : "warning",
                    showCancelButton     : true,
                    confirmButtonColor   : "#DD6B55",
                    confirmButtonText    : "Oui",
                    closeOnConfirm       : typeof callback == 'function'
                }, function( isConfirm ) {
                    callback( isConfirm );
                });
            }

            /**
             *  Alert Warning
             *  @param string message
             *  @return void
            **/

            this.warning            =   function( message ) {
                return SweetAlert.swal(
                    {
                    title                : "Attention",
                    text                 : message,
                    type                 : "warning",
                    showCancelButton     : false,
                    confirmButtonColor   : "#DD6B55",
                    confirmButtonText    : "Ok",
                    closeOnConfirm       : true
                }
                );
            }
        }
    }]);


    <?php
        global $Options;
        $this->load->config( 'rest' );
    ?>
    tendooApp.factory( 'permissionsResource', function( $resource ) {
        return $resource(
            '<?php echo site_url( [ 'rest', 'perm_manager', 'permissions/id']); ?>',
            {
                id              :   '@_id'
            },{
                get : {
                    method : 'GET',
                    headers			:	{
                        '<?php echo $this->config->item('rest_key_name');?>'	:	'<?php echo @$Options[ 'rest_key' ];?>'
                    }
                },
                save : {
                    method : 'POST',
                    headers : {
                        '<?php echo $this->config->item('rest_key_name');?>'	:	'<?php echo @$Options[ 'rest_key' ];?>'
                    }
                },
                delete : {
                    method : 'DELETE',
                    headers : {
                        '<?php echo $this->config->item('rest_key_name');?>'	:	'<?php echo @$Options[ 'rest_key' ];?>'
                    }
                }
            }
        );
    });


    tendooApp.controller( 'permManagerController', [ '$scope', '$http', 'sharedAlert', 'permissionsResource', function ( $scope, $http, sharedAlert, permissionsResource ){
        
        $scope.roles = {};
        $scope.permissions = {};
        $scope.add = {};

        /**
         * Load Data
         **/

         $scope.loadData = function(){
             $http.get( '<?php echo site_url( [ 'dashboard', 'perm_manager', 'get' ] );?>' ).then( function( returned ){
                $scope.roles = returned.data.roles;
                $scope.permissions = returned.data.permissions;
             });
         }

         /**
          * Delete selected element 
          **/

         $scope.bulkDelete = function (){

             var bulkDel = [];
             
             _.each( $scope.roles, function( role ){
                _.each( role.permissions, function ( permission ){
                    if( permission.checked == true){
                       bulkDel.push( [permission.perm_name, role.name] );
                    }
                });
             });

             if ( bulkDel.length == 0){
                return sharedAlert.warning( '<?php echo _s( 'Sélectionnez au moins un élément', 'perm_manager' );?>' );   
             } else {
                sharedAlert.confirm( '<?php echo _s( 'Souhaitez-vous supprimer ces élément ?', 'perm_manager' );?>', function( action ) {
                    if( action ) {
                        permissionsResource.delete( {'entries[]' : bulkDel}, function( data ) {
                            $scope.loadData();
                        },function(){
                            sharedAlert.warning( '<?php echo _s(
                                'Une erreur s\'est produite durant l\'operation',
                                'perm_manager'
                            );?>' );
                        });
                    }
                });
             }
         }

         /**
          * Delete one element 
          **/

         $scope.delete = function ( permission, group){
             sharedAlert.confirm( '<?php echo _s( 'Souhaitez-vous supprimer ces élément ?', 'perm_manager' );?>', function( action ) {
                if( action ) {
                    var entries = {};
                    entries = { permission : permission, group : group };
                    permissionsResource.delete( entries, function( data ) {
                        $scope.loadData();
                    },function(){
                        sharedAlert.warning( '<?php echo _s(
                            'Une erreur s\'est produite durant l\'operation',
                            'perm_manager'
                        );?>' );
                    });
                }
            });
         }

         /**
          * Add a permission to a role
         **/

        $scope.addPermission = function (){
            permissionsResource.save( 
                $scope.add, 
                function(){
                    $scope.loadData();
                }, 
                function( returned ){
                    if( returned.data.status === 'alreadyExists' ) {
                        sharedAlert.warning( '<?php echo _s( 'Le role possède déja cette permission', 'nexopos_advanced' );?>' );
                    }

                    if( returned.data.status === 'forbidden' || returned.status == 500 ) {
                        sharedAlert.warning( '<?php echo _s( 'Une erreur s\'est produite durant l\'opération.', 'nexopos_advanced' );?>' );
                    }
                }
            )
        }

         $scope.loadData();
    } ]);  
</script>