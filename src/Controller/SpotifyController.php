<?php
/**
 * @file
 * @author Alejandro Suarez
 * Contains \Drupal\spotify\Controller\SpotifyController.
 */
namespace Drupal\spotify\Controller;
/**
 * Provides route responses for the Example module.
 */
class SpotifyController {
  /**
   * Returns albums page info
   *
   * @return array
   *   A simple renderable array.
   */
  public function getReleases() {

    $url = 'https://api.spotify.com/v1/browse/new-releases';
    $resp = $this->makeCurl($url);

    if (isset($resp->error)) {
      $renderable = array(
        '#markup' => 'The access token expired',
      );
    }else{

      $albums = $resp->albums->items;
      $data = array();

      foreach ($albums as $key => $album) {
        $artists = array();
        foreach ($album->artists as $key => $artist) {
          $artists[] = array(
            'id' => $artist->id,
            'name' => $artist->name,
          );
        }
        $data[] = array(
          'artists' => $artists,
          'image' => $album->images[1]->url,
          'name' => $album->name,
        );
      }
      
      $renderable = array(
        '#theme' => 'albums',
        '#var' => $data,
      );
    }

    return $renderable;
  }

  /**
   * Returns artist page info
   *
   * @return array
   *   A simple renderable array.
   */
  public function getArtist($artist) {

    $url = 'https://api.spotify.com/v1/artists/'.$artist;
    $resp = $this->makeCurl($url);

    if (isset($resp->error)) {
      $renderable = array(
        '#markup' => 'The access token expired',
      );
    }else{

      $data = array();

      $data = array(
        'name' => $resp->name,
        'image' => $resp->images[2]->url
      );

      $url = 'https://api.spotify.com/v1/artists/'.$artist.'/top-tracks?country=CO';
      $resp = $this->makeCurl($url);

      foreach ($resp->tracks as $key => $track) {
        $data['tracks'][] = array(
          'image' => $track->album->images[2]->url,
          'name' => $track->album->name,
          'song' => $track->name,
        );
      }

      $renderable = array(
        '#theme' => 'artist',
        '#var' => $data,
      );
    }

    return $renderable;
  }

  public function makeCurl($url){
    // Get cURL resource
    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
      )
    );

    // set headers
    $headers = array(
      'Accept: application/json',
      'Content-Type: application/json',
      'Authorization: Bearer '. \Drupal::state()->get('test_content_types', 'BQBgmLEiAQrD94WqqxklMY0Gbxs0Pt5FveZm4CJ_RNqAc-xUVAnHgOD8xhXXjMpzsdF6-6L_ppHdaPeRBYOHGndGqi2108orsRsh2HH0aGgPJD4WNRt_CfNDMTFRS6Ld2DA6azzQEq9MtFE'),
    );

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    // Close request to clear up some resources
    curl_close($curl);

    // iterate response from Spotify API to pass to template
    $resp = json_decode($resp);

    return $resp;
  }
}
?>