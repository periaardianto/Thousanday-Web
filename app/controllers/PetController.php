<?php
use Phalcon\Assets\Filters\Cssmin;

class PetController extends ControllerBase {

    public function indexAction() {
        $this->assets->collection( 'header' )
            ->setTargetPath( '../public/production/pet.css' )
            ->addCss( '../public/css/globe.css' )
            ->addCss( '../public/css/general.css' )
            ->addCss( '../public/css/pet.css' )
            ->setTargetUri( '/../production/pet.css' )
            ->join( true )->addFilter( new Cssmin() );
    }

    //* read information for one pet
    public function readAction() {
        $id = $this->request->get( 'id' );
        try {
            $db = DbConnection::getConnection();
            $Pet = new Pet( $db );
            $pet = $Pet->readOnePet( $id );
            if ( !$pet ) {
                return $this->response->setStatusCode( 404, 'Not Found' );
            } 
            $Moment = new Moment( $db );
            $moments = $Moment->readPetMoments( $id, 0 );
            $Watch = new Watch( $db );
            $watchs = $Watch->readPetWatchs( $id );
            if ( isset( $pet[ 'relative_id' ] ) ) {
                //if pet has relative
                $User = new User( $db );
                $family = $User->readPetFamily( $pet[ 'owner_id' ], $pet[ 'relative_id' ] );
                $friends = $Pet->readPetFriends( $pet[ 'owner_id' ], $pet[ 'relative_id' ], $id );
                return json_encode( [ $pet, $family, $friends, $moments, $watchs ] );
            } else {
                //if pet do not have relative
                $User = new User( $db );
                $family = $User->readUserName($pet['owner_id']);
                $friends = $Pet->readUserPets( $pet[ 'owner_id' ], $id );
                echo json_encode( [ $pet, [ $family ], $friends, $moments, $watchs ] );
            }
        } catch ( Exception $e ) {
            return $this->response->setStatusCode( 500, 'Internal Server Error' );
        }
    }

    //* load more pets moments
    public function loadAction() {
        $pet = $this->request->get( 'pet' );
        $load = ( int ) $this->request->get( 'load');
        $add = ( int ) $this->request->get( 'add' );
        try {
            $db = DbConnection::getConnection();
            $Moment = new Moment( $db );
            $moments = $Moment->readPetMoments( $pet, $load, $add );
            return json_encode( $moments );
        } catch ( Exception $e ) {
            return $this->response->setStatusCode( 500, 'Internal Server Error' );
        }
    }

    //* user watch or unwatch a pet
    public function watchAction() {
        $data = $this->request->getJsonRawBody( true );
        $token = $data[ 'token' ];
        $pet = ( int ) $data[ 'pet' ];
        $user = ( int ) $data[ 'user' ];
        $action = ( int ) $data[ 'action' ];
        try {
            $db = DbConnection::getConnection();
            $Token = new Token( $db );
            $validation = $Token->checkUserToken( $user, $token );
            if ( $validation !== 1 ) { 
                return $this->response->setStatusCode( 403, 'Forbidden' );
            }
            $Watch = new Watch( $db );
            $db->beginTransaction();
            if ($action === 1) {
                //add watch for current pet
                $add = $Watch->createUserWatch( $pet, $user );
                if ( $add !== 1 ) {
                    $db->rollBack();
                    return $this->response->setStatusCode(500, 'Internal Server Error');
                }
            } else {
                //remove watch
                $delete = $Watch->deleteUserWatch( $pet, $user );
                if ( $delete !== 1 ) {
                    $db->rollBack();
                    return $this->response->setStatusCode(500, 'Internal Server Error');
                }
            }
            $db->commit();
            return $this->response->setStatusCode( 201, 'Success' );
        } catch ( Exception $e ) {
            return $this->response->setStatusCode( 500, 'Internal Server Error' );
        }
    }

}