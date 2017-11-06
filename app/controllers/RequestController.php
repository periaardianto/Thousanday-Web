<?php
use Phalcon\Assets\Filters\Cssmin;

class RequestController extends ControllerBase {

    public function indexAction() {
        $this->assets->collection( 'header' )
            ->setTargetPath( '../public/production/request.css' )
            ->addCss( '../public/css/globe.css' )
            ->addCss( '../public/css/general.css' )
            ->addCss( '../public/css/request.css' )
            ->setTargetUri( '/../production/request.css' )
            ->join( true )->addFilter( new Cssmin() );
    }

    //* read data for request page
    public function readAction() {
        $id = $this->request->get( 'id' );
        try {
            $db = DbConnection::getConnection();
            $Request = new Request( $db );
            $requests = $Request->readUserRequests( $id, 0 );
            return json_encode($requests);
        } catch ( Exception $e ) {
            return $this->response->setStatusCode( 500, 'Internal Server Error' );
        }
    }

    //* accept one request
    public function acceptAction() {
        $data = $this->request->getJsonRawBody( true );
        $token = $data[ 'token' ];
        $user = ( int ) $data[ 'user' ];
        $pet = ( int ) $data[ 'pet' ];
        try {
            $db = DbConnection::getConnection();
            $Token = new Token( $db );
            $validation = $Token->checkUserToken( $user, $token );
            if ( $validation !== 1 ) { 
                return $this->response->setStatusCode( 403, 'Forbidden' );
            }
            $Request = new Request($db);
            $db->beginTransaction();
            $delete = $Request->deleteUserRequest( $user, $pet );
            if ( $delete !== 1 ) {
                $db->rollBack();
                return $this->response->setStatusCode( 500, 'Internal Server Error' );
            }
            $Pet = new Pet( $db );
            $action = $Pet->addPetRelative( $pet, $user );
            if ( $action !== 1 ) {
                $db->rollBack();
                return $this->response->setStatusCode( 500, 'Internal Server Error' );
            }
            $db->commit();
            return $this->response->setStatusCode( 201, 'Success' );
        } catch ( Exception $e ) {
            return $this->response->setStatusCode( 500, 'Internal Server Error' );
        }
    }

    //* delete one request
    public function deleteAction() {
        $data = $this->request->getJsonRawBody( true );
        $token = $data[ 'token' ];
        $user = ( int ) $data[ 'user' ];
        $pet = ( int ) $data[ 'pet' ];
        try {
            $db = DbConnection::getConnection();
            $Token = new Token( $db );
            $validation = $Token->checkUserToken( $user, $token );
            if ( $validation !== 1 ) { 
                return $this->response->setStatusCode( 403, 'Forbidden' );
            }
            $Request = new Request($db);
            $db->beginTransaction();
            $delete = $Request->deleteUserRequest( $user, $pet );
            if ( $delete !== 1 ) {
                $db->rollBack();
                return $this->response->setStatusCode( 500, 'Internal Server Error' );
            }
            $db->commit();
            return $this->response->setStatusCode( 201, 'Success' );
        } catch ( Exception $e ) {
            return $this->response->setStatusCode( 500, 'Internal Server Error' );
        }
    }

}