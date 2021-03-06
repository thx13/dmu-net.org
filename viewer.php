<?php

/*
########################### DMU-Net.org ##########################

* Maintainer: Jonathan DEKHTIAR
* Date: 2017-05-17
* Contact: contact@jonathandekhtiar.eu
* Twitter: https://twitter.com/born2data
* LinkedIn: https://fr.linkedin.com/in/jonathandekhtiar
* Personal Website: http://www.jonathandekhtiar.eu
* RSS Feed: https://www.feedcrunch.io/@dataradar/
* Tech. Blog: http://www.born2data.com/
* Github: https://github.com/DEKHTIARJonathan

*******************************************************************

 2017 May 17

 In place of a legal notice, here is a blessing:

    May you do good and not evil.
    May you find forgiveness for yourself and forgive others.
    May you share freely, never taking more than you give.

*******************************************************************
*/

    header( "Last-Modified: " . gmdate( "D, d M Y H:i:s") . " GMT");
    header( "Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
    header( "Cache-Control: post-check=0, pre-check=0", false);
    header( "Pragma: no-cache"); // HTTP/1.0
    header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

    $part_name = isset($_GET['part_name']) ? $_GET['part_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>DMU-Net 3D Viewer</title>

        <meta name='Keywords' content='WebGl,pythonOCC'>
        <meta charset="utf-8">

        <meta Http-Equiv="Cache" content="no-cache">
        <meta Http-Equiv="Pragma-Control" content="no-cache">
        <meta Http-Equiv="Cache-directive" Content="no-cache">
        <meta Http-Equiv="Expires" Content="0">

        <style type="text/css">
            body {
                background-color: #fff;
                margin: 0px;
                overflow: hidden;
            }

            #info {
                position: absolute;
                top: 96%;
                width: 96%;
                color: #fff;
                padding: 5px;
                font-family: Monospace;
                font-size: 13px;
                text-align: right;
                opacity: 1;
                }

            #pythonocc_rocks {
                padding: 5px;
                position: absolute;
                left: 1%;
                top: 85%;
                height: 60px;
                width: 305px;
                border-radius: 5px;
                border: 2px solid #f7941e;
                opacity: 0.7;
                font-family: Arial;
                background-color: #fff;
                color: #ffffff;
                font-size: 16px;
                opacity: 0.7;
            }

            a {
                color: #f7941e;
                text-decoration: none;
            }

            a:hover {
                color: #ffffff;
            }

            #loader {
              position: absolute;
              left: 50%;
              top: 50%;
              z-index: 1;
              width: 150px;
              height: 150px;
              margin: -75px 0 0 -75px;
              border: 16px solid #f3f3f3;
              border-radius: 50%;
              border-top: 16px solid #3498db;
              width: 120px;
              height: 120px;
              -webkit-animation: rotate 2s linear infinite;
              animation: rotate 2s linear infinite;
            }

            @keyframes rotate {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            @-webkit-keyframes rotate  {
                0% { -webkit-transform: rotate(0deg); }
                100% { -webkit-transform: rotate(360deg); }
            }

            @-moz-keyframes rotate {
                0% {-moz-transform:  rotate(0deg); }
                100% {-moz-transform:  rotate(360deg); }
            }

            @-o-keyframes rotate {
                0% {-moz-transform: rotate(0deg);}
                100% {-moz-transform: rotate(360deg);}
            }

            @keyframes rotate {
                0% {transform: rotate(0deg);}
                100% {transform: rotate(360deg);}
            }

            #myProgress {
                width: 100%;
                background-color: grey;
                margin-top: 50vh;
                transform: translateY(-50%);
            }
            #myBar {
                width: 1%;
                height: 30px;
                background-color: green;
                text-align: center;
                line-height: 30px;
                color: white;
            }
            #progress_mask {
                width: 100vw;
                height: 100vh;
                background-color: white;
                z-index: 9999;
            }
        </style>
    </head>

    <body onload="removePreloader()">
        <div id="loader"></div>
        <div id="progress_mask" style="display:none;"><div id="myProgress"><div id="myBar">0%</div></div></div>
        <div id="container" style="display:none;"></div>

        <script>
            /* Function removing the page loader */
            function removePreloader() {
              var el = document.getElementById("loader");
              el.parentNode.removeChild( el );

              document.getElementById("progress_mask").style.display = "block";
            }

            /* Moving the progress bar of the CAD model */

            function updateProgressBar(progress){

                var elem = document.getElementById("myBar");
                var progress = parseInt(progress);

                console.log( progress + '% loaded' );

                if (progress >= 100) {
                    var el = document.getElementById("progress_mask");
                    el.parentNode.removeChild( el );
                    document.getElementById("container").style.display = "block";
                } else {
                    elem.style.width = progress + '%';
                    elem.innerHTML = progress * 1  + '%';
                }
            }
        </script>

        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/three.js/84/three.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/stats.js/r16/Stats.min.js"></script>
        <script type="text/javascript" src="js/OrbitControls.js"></script>

        <script type="text/javascript">
            var camera, scene, renderer, object, stats, container, shape_material;
            var targetRotation = 0;
            var targetRotationOnMouseDown = 0;
            var targetRotationY = 0;
            var targetRotationYOnMouseDown = 0;
            var mouseX = 0;
            var mouseXOnMouseDown = 0;
            var mouseY = 0;
            var mouseYOnMouseDown = 0;
            var moveForward = false;
            var moveBackward = false;
            var moveLeft = false;
            var moveRight = false;
            var moveUp = false;
            var moveDown = false;
            var windowHalfX = window.innerWidth / 2;
            var windowHalfY = window.innerHeight / 2;

            init();
            animate();

            function init() {
                console.log("script init ...");

                container = document.createElement( 'div' );
                document.body.appendChild( container );

                camera = new THREE.PerspectiveCamera( 50, window.innerWidth / window.innerHeight, 1, 99999 );

                controls = new THREE.OrbitControls( camera );
                controls.addEventListener( 'change', light_update );

                scene = new THREE.Scene();
                scene.add( new THREE.AmbientLight(0x101010));
                directionalLight = new THREE.DirectionalLight( 0xBDBDBD );
                directionalLight.position.copy( camera.position );
                scene.add( directionalLight );
                light1 = new THREE.PointLight( 0xffffff );
                scene.add( light1 );

                phong_material = new THREE.MeshPhongMaterial( {
                    color: 0x6E6E6C,
                    specular: 0x555555,
                    shininess: 5,
                    precision: "highp",
                    depthWrite: true,
                });
                

                var loader = new THREE.BufferGeometryLoader();

                // load a resource
                loader.load(
                    // resource URL
                    <?php echo "'dataset/".$part_name."/shape.json',"; ?>
                    // Function when resource is loaded
                    function ( geometry ) {
                        object = new THREE.Mesh(geometry , phong_material);
                        object.overdraw = true;

                        geometry.center();
                        geometry.computeBoundingSphere();
                        radius = geometry.boundingSphere.radius;
                        aspect = 80 / 60;
                        distanceFactor = Math.abs( aspect * radius / Math.sin( camera.fov/2 ));

                        camera.position.set( 0, 0, distanceFactor/5 );

                        scene.add( object );
                    },
                    // Function called when download progresses
                    function ( xhr ) {
                        updateProgressBar(xhr.loaded / xhr.total * 100);
                    },
                    // Function called when download errors
                    function ( xhr ) {
                        console.log( 'An error happened' );
                    }
                );

                renderer = new THREE.WebGLRenderer({antialias:true});
                renderer.setClearColor("#ffffff");
                renderer.setSize( window.innerWidth, window.innerHeight );
                container.appendChild( renderer.domElement );

                stats = new Stats();
                stats.domElement.style.position = 'absolute';
                stats.domElement.style.top = '0px';
                container.appendChild( stats.domElement );
                window.addEventListener( 'resize', onWindowResize, false );
            }

            function animate() {
                requestAnimationFrame( animate );
                controls.update();
                render();
                stats.update();
            }
            function render() {
                renderer.render( scene, camera );
            }
            function onWindowResize() {
                camera.aspect = window.innerWidth / window.innerHeight;
                camera.updateProjectionMatrix();
                renderer.setSize( window.innerWidth, window.innerHeight );
            }
            function light_update(){
                directionalLight.position.copy( camera.position );
            }
        </script>
    </body>
</html>
