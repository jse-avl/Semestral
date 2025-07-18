<?php
function fetchPopularMovies($genreId = null, $page = 1) {
    $url = "https://api.themoviedb.org/3/discover/movie?language=es-ES&sort_by=popularity.desc&page=$page";
    if ($genreId) {
        $url .= "&with_genres=$genreId";
    }

    $headers = [
        "Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJmOTk0NzY5NDA0M2EyNTY3ZmE1ZWUwY2Q4OTA2ZGEwOCIsIm5iZiI6MTc1MTM5ODU3NS42NDMwMDAxLCJzdWIiOiI2ODY0MzhhZjUxZjc3OThkZTRkMTI1MDEiLCJzY29wZXMiOlsiYXBpX3JlYWQiXSwidmVyc2lvbiI6MX0.vx91c1ypvxjGRzz26P_SNsnqPAOGLEFou3Lo8dGKU58",
        "Accept: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return $data['results'] ?? [];
}

function searchMovies($query) {
    $url = "https://api.themoviedb.org/3/search/movie?language=es-ES&query=" . urlencode($query);

    $headers = [
        "Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJmOTk0NzY5NDA0M2EyNTY3ZmE1ZWUwY2Q4OTA2ZGEwOCIsIm5iZiI6MTc1MTM5ODU3NS42NDMwMDAxLCJzdWIiOiI2ODY0MzhhZjUxZjc3OThkZTRkMTI1MDEiLCJzY29wZXMiOlsiYXBpX3JlYWQiXSwidmVyc2lvbiI6MX0.vx91c1ypvxjGRzz26P_SNsnqPAOGLEFou3Lo8dGKU58",
        "Accept: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return $data['results'] ?? [];
}

function syncMoviesToDB($genreId, $page = 1) {
    require_once 'db.php'; // Asegúrate de tener la conexión PDO aquí

    $url = "https://api.themoviedb.org/3/discover/movie?language=es-ES&sort_by=popularity.desc&page=$page&with_genres=$genreId";
    $headers = [
        "Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJmOTk0NzY5NDA0M2EyNTY3ZmE1ZWUwY2Q4OTA2ZGEwOCIsIm5iZiI6MTc1MTM5ODU3NS42NDMwMDAxLCJzdWIiOiI2ODY0MzhhZjUxZjc3OThkZTRkMTI1MDEiLCJzY29wZXMiOlsiYXBpX3JlYWQiXSwidmVyc2lvbiI6MX0.vx91c1ypvxjGRzz26P_SNsnqPAOGLEFou3Lo8dGKU58",
        "Accept: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $guardadas = 0;

    foreach ($data['results'] as $movie) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO movies (id, title, overview, poster_path, release_date, genre_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $movie['id'],
            $movie['title'],
            $movie['overview'],
            $movie['poster_path'],
            $movie['release_date'],
            $genreId
        ]);
        $guardadas++;
    }

    return $guardadas;
}
function getMovieById($movieId) {
    $url = "https://api.themoviedb.org/3/movie/$movieId?language=es-ES";

    $headers = [
        "Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJmOTk0NzY5NDA0M2EyNTY3ZmE1ZWUwY2Q4OTA2ZGEwOCIsIm5iZiI6MTc1MTM5ODU3NS42NDMwMDAxLCJzdWIiOiI2ODY0MzhhZjUxZjc3OThkZTRkMTI1MDEiLCJzY29wZXMiOlsiYXBpX3JlYWQiXSwidmVyc2lvbiI6MX0.vx91c1ypvxjGRzz26P_SNsnqPAOGLEFou3Lo8dGKU58",
        "Accept: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return is_array($data) ? $data : [];
}
?>