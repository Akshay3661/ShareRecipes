<?php
session_start();
include("./config/Database.php");

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$firstName = $isLoggedIn ? $_SESSION['firstName'] : '';
$lastName = $isLoggedIn ? $_SESSION['lastName'] : '';

$db = new Database();
$conn = $db->getConnection();

function logout()
{
    $_SESSION = array();
    session_destroy();
    header("Location: ./index.php");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['logout'])) {
    logout();
}

try {
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['search'])) {
        $searchTerm = $_GET['search'];

        $query = "
        SELECT
            recipe.id AS recipeId,
            recipe.name AS recipeName,
            recipe.description AS recipeDescription,
            recipe.category_Id AS categoryId,
            CONCAT(users.firstName, ' ', users.lastName) AS creatorFullName,
            category.categories AS foodCategory,
            images.images AS recipeImages
        FROM
            recipeTable AS recipe
        INNER JOIN
            usersTable AS users ON recipe.user_Id = users.id
        INNER JOIN
            categoryTable AS category ON recipe.category_Id = category.id
        LEFT JOIN
            imagesTable AS images ON recipe.id = images.recipe_Id
        WHERE
            recipe.status = 'Published' AND
            (recipe.name LIKE ? OR recipe.description LIKE ? OR category.categories LIKE ?)
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Query preparation failed: " . $conn->error);
        }

        $searchTerm = "%$searchTerm%";
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    } else {
        $query = "
        SELECT
            recipe.id AS recipeId,
            recipe.name AS recipeName,
            recipe.description AS recipeDescription,
            recipe.category_Id AS categoryId,
            CONCAT(users.firstName, ' ', users.lastName) AS creatorFullName,
            category.categories AS foodCategory,
            images.images AS recipeImages
        FROM
            recipeTable AS recipe
        INNER JOIN
            usersTable AS users ON recipe.user_Id = users.id
        INNER JOIN
            categoryTable AS category ON recipe.category_Id = category.id
        LEFT JOIN
            imagesTable AS images ON recipe.id = images.recipe_Id
        WHERE
            recipe.status = 'Published'
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Query preparation failed: " . $conn->error);
        }
    }

    $stmt->execute();
    if (!$stmt) {
        die("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if (!$result) {
        echo "Error in executing query: " . $stmt->error;
        exit;
    }

    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        $row['recipeName'] = htmlspecialchars($row['recipeName']);
        $row['recipeDescription'] = htmlspecialchars($row['recipeDescription']);
        $row['foodCategory'] = htmlspecialchars($row['foodCategory']);
        $row['creatorFullName'] = htmlspecialchars($row['creatorFullName']);
        $recipes[] = $row;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="data:image/jpeg;base64,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">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/style.css">

    <title>RecipePoint</title>
</head>

<body>
    <header>
        <section class="wrapper nav-flex">
            <nav>
                <a href="./index.php"><img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBw8PEBANDRIQEA8PDw4QEA4PEBUQDxAQFRUXFhcVFRUZHCghGBolHRUVITMhJTUrLi4uGCAzODMtNygtLisBCgoKDg0OGhAQGC0dHR8tLS0tLi0tLS0tLSstLS0tLSsrLSstLS0tLSstLS0tLSstLS0tLS0tLS0tLS0tLS0tLf/AABEIAMMBAwMBIgACEQEDEQH/xAAcAAACAgMBAQAAAAAAAAAAAAAAAwIEAQYHBQj/xABBEAACAQMBBQYEAwUGBQUAAAABAgADBBESBQYhMVEHE0FhcaEiMoGRFFKxI0JygsEkM3OSsvBDYqLR4SU0RFNj/8QAGwEBAQEBAQEBAQAAAAAAAAAAAAIBAwQFBgf/xAA1EQACAQMBBQYFAgYDAAAAAAAAARECAwQhBRIxQVFhcYGRocETIrHR8CMyBhQzUuHxFUKi/9oADAMBAAIRAxEAPwDRIQhLP6IEIQgBCEIAQmQJJVgAFkwsyFjQsxskgFkwskFkgskkgFkgsYFkgsGSL0w0xumGmDJFaZjTHaZnTAkr6ZgrLGmQKxJsiSsiVlgrIFYNK5WQKywVkGWVJUlYiYjWWQImlEYQhACEIQAhCEAIQhACEIQAhCEAJkQklEAyojFWCrHKswlswqxgWZVZNVkkEQsYFkgskFmEkQsyFjQsyFmSZIvTDTPQsNnVK7aUHLm54Aepmx09wblhlT9CNI94hnlvZlm092utJ+L+iZpumY0zZLrc6/pHBos4yBqpkOMk4HnNm2R2dYUNdMNR46BlgPIkEZ+81HO7tCxbpVW9M8I1/wBeMHNCsiVnYqu4FqRgYHnpIP3Bmpbw7i1qANWgDVQcSq/EwHUDHH/fOGjlZ2tYuOHNPfw8036mklZArLDLIFYPqSVysWyyyVi2WaVJXZYplllli2WaiisRIxzCLIlFkYQhACEIQAhCEAIQhACEJkQCSiMUSKiPUTJJMqI1RMKIxRJIBVjFWCiNUTCDCrGBZlVjAsklswFk6dMkgAEknAAGST0AmQs3fcLZSnNy4ywyFz4cSM+vA+0JSeXKyace262p5JdX+fkwj2dzdmfh6KM64qZBIPMN458/D6TcVbIBHjPKRcfcn7nP9Z6Nr8g+v6zpB+Qrrddbqq4ttjoSJYZA8TnElMMCEJDWM48enSAaRvVuIlw5r2umlUbJdeSO3UflbqeR6Z4zml/ZVKFRqNUaWTmPLwI8uB+0+hZzPtetUH4W4GA7VDSbzUgnj14gfaY0fY2dnXFcptVuaXouq6a9OX+jnTLIMssMsUyzD9ImV2WKYS0wimEotFZhEsJaYRLCUi0IMxJMJGaUEIQgBCEIAQhCAEmokRGIIMJqI5RIoI1RJZDJqIxRIqJ62zNg3VzxoUKjqeTqmEP8xwvvJZzqqVKluEUFEaqzZaW4W0iMmkg8mrJn2JEq3m7F9bgtVoOFHNkxUUDqSpOB6yTzrJstwq033r7nkKIwCZUSYEw6GAJvG4e0VCtbtwOcr5jJP6kj7dZpYEbRZ0Iem2h14q3P6EeIPSanB5MzH/mLTo4Piu//ADqvE7FMVtqpSAQKWfHBRxM0Oy34qIAtegzkfvUirA/c5H1z6mW9nb1tc3A/s7UFVThnqBnqDIypQDhzJ5nlOkpn5qvCyKE3VRCWr4Gz/i7hzq7sL01NjH0nqUKtYj4lXPUE4ikZEQ1qrKtNQWLuQqKo5sSeAEdaXlKsuujUp1VzjVTcOM9Mjxg86pcTGhM03PNgPJR/WJqEUFNRjlRjVw48TjhLs8fem4FO0rVT8qKHPop1f0gyOh4+0u0Kyos9IF3qpwZVQ4BxnGes5xvLt2rtKqjspp0aZ1IrcCx8DjoP6nrwo2SsVNV/mqu9Rz1LHMawkOo/VY2zrVipVaupc315xCXuV2WKYSyw8jFMJJ9ArMIphNm2Luld3g10kC0jyq1ToQ/w8CW9QMec949ldbGfxKavy922P82f6S0ca87HtvdrrSfi/omc2YRbCe9vDu3dWJAuEGgnCVEOqm56BsAg+RAM8JhNPVbrprpVVLlPmiuwizHuIlhLOyIwhCDQhCEAIQhAJLGoIpY5JjJY5RL+y9nVbmqtG3Qs7Hgo8B4knwA6yigzw6zum4u7S2NuC4H4iqA1VvFfEUx5L7nJ6STwZ2Ysa3vcW+C9+5fYqbtbhW1qA9wFuK/P4hqpKf8AlU8/Vvpie1tbeWysxi4r00IwO7HxOOnwrkj6zQd/O0By72ezSV0krUul8W5EUyOnXmfDA4nnNWk+BUqayXLYd8/Gw6E8zx94SPDj7JvZcXsqtqeC5/ZdyXkzsb9qWzhwC3LD8y00IP3fM9nYm+NhekJRq6ah5UqvwOT0HgT5AmcVtN3L2rR/E0aLVKXxfGuHPA4PBT5dJ5isVYEEqysDkcCCPEGal2nrewsO4mrdblacU4fauXdozte+m6qVUe5t1C10BZ0QcKyjJPAfv+OfHl0I5wmCARxB4gjkROr7h7Wa7sqVWoc1F/Zu3ixXkx8yMGebbbhUu9rVKzE02rVXpUqfw6UZtQVm8skADGABxkNHy8bMePv2ch/s0XPg4a90c/Akws6e25diRgI6n8y1Gz7kieba7iotYl3L0Bgqvy1CfysQOXmOflM3WeinamO02213rj3RJo1OkWOFBY9FGT7S7symyV1DAqePAgg4PrOpvUtrRAGajbJyGpkpKT9cZMA9vdodLUq6A86bLUAI6EcjNS1PJd2m67dS+G91pqZ6qOkeBrO+et9iVtOcinTyPHStRc+wzNQ7IEq/inZCwpCmRUBJ0MSeGR14Eg+vWdU2lWt6VFvxJpLQK92wqYCFSMaMHnkeEo7s2lhTptU2foNGoxJZWLDI4YyeI5DgZT4nKxm/CwLlndfzPjGmsTr1008+WvuEgcTw85Q23s2neW9S2qFglZcFkIDAcwQfXE4nvrvPV2hcMqM621OoUpIpIUkHGph4nx+uJtWy9vjYthTSqWr3FcCrTti390jDmxxw+XPmc+sHW5si7aooqT/UqelMeLczy56R2jb/AHAuKSZoulbSvyDKOcflBJB+4ns7t7kUqYWreqKlXgRSPGmnk35z7evOM3J31XaJek9PuqyDVpDa1ZQQDg44EZHDziO0fe1rGmLe3P8Aaq6nB4HuafLXjqTnHofKZBtVzOru/wArVpU/DTw0juS6dhtYtLZcUhTogY4UwiDh/DieNfbm2NWrTrd2E0tl0p4WnU8mXGBxxyxniDOJX1rc09F1cGr+31MlR3JZypGTnPPiOfUTtvZ9tB7iwovVYs4BUsTlmAPAk+JxjjNgZeDcwravW70y4cdYfa1yc8I7+FneLeC22bRFSucfu0qSAamx4KOQA4ZPIcOoE0ml2t5qAG0IpZxkVcuB1Bxgny9553bKG/G0dWdP4UaPy51Pq+vL2h2cbBsb5ayXILVkYOo1afgIxkY58T7iOJ6sbBxLWGsi/S654xynxXqzpm1LOltCzdOa16Wqm2OKsRlGHmDgz55M+gdu7RpbNsnqngtKnoopniz4wiD/AHwAJ8J8/hcDjz8fWDNh727X/bKjv1n0j0FOIpxHNEuJSPvoVCZmJpQQhCAEIQgE0lhIhI9JjJZ7e6FBal/aU2+U3FMkeB0tnH104+s7Jv3ePQ2ddVaWQwp6cjmAzBSQfAgMZwzZ101CrTr0/mpPTdc8tQYMM+XCd72VtK22lb6001EdcVaLYJQkcUdf955iSfn9rJ0XrV5071KifBzHjyOWdnW6gvGNauP2NNv87c8H2+/rH9rrItxbWtNQqUaGsKowAWLDGP4VH3nWtn2FG3Tu6CLTQcQq8BOFb/XXfbTumzwRxRA6d2Ahx9QYXE7YGRVm57uv9tKe6uk6ebUy/DhB1DssXGzqZ6s5/wCoict37pLT2ldpTACmoGwOQd1Jb9TOkbpbZtbLZlPvK1EvTpVKhoiqnesclgoTOcnPvOfbu7MrbWvXrPyeoatZxnSAWJwOhOTjpk9IRWBV8PKycmvShOrxmqVHXs7WkdM7MLNqVgmoYNQl8euAPuOP1lPtA33/AAX9ltdL3LLkk8Vog8sjxY88Hy6zcCUtqJPAJQpknHDCoM/oJ8+0L9a17+KvD8NSuXrEg47vVqxjpjPCDw7Px1m37mRdUpNuOrcuO2Oncevs/fzadKsrVaz1BqXvKTqujT04cvUTr21tuUra0a+f5BTDqvi5YZUfXI/WcgvEO2NpM9nTYUiaI1sv7icC7Y64HD0E2Ttermlb2VinIsWYeSDSv6tB68zGs37+PbVCoqqU1UrkoThwlro0n7QaXe1LzabXF7VLOKSam+IhEpFsDC+A4nh5Hxnv9klZ1vGVSSj0m1qPlxkYJHkT+s9Xsy2ZTubO7ouWArEKxBGsLgEYJB8czZbTZdjsShVueJwCdTka6jcSEXHDJ/8AJ5ZgvOzqN27h00a6U0pLsXb14KOnazmG/u33v7qpxP4egzJRXPwnSSC3qSM+mB4Tddw6b09j3lTrTuXT+WkR+qmcpL62ZgAoeozBRyUFs4E6jvJtpdnbMpWNLBubigFKD91GUhmPTPED1J8Ia0g9G0LKptWcW1TxqXlTxfrP+WcvtmVWVn4gaSy9RqyRL21KVzV/9QuEIWuz6XKlQMcSo+nD0HlKFvgMmriodc+Y1cZvnajtai34bZtDAFHFSooXCISqhB9FJ+46TeZ779yqnItU00zvKqX0pUT5trxjTg1nsftGNzUr/upT05820lfYN9xNc33vTX2lc1DySt3adAtM6Bjpnn9ZsVjvZa7OsFt7Bu+vKo+N9OlaT6RknIGojgB4eJ6HR7qlVR2FcOKuQ7hsg5cA548eIIMxcTz4lFdeZcyKlup/LSno4piXD5PR9dde3aKn4vbJtbajR7uhaKEVznHyhWYtjngDgOk2nfSo2ytmULS2c06lRwhZW0uVwSx4HIyxXOOuPGePs3tK/DWqUKdtqrU0Cd4WxTOBgMVHEnyyPWeDf220NpLW2lWV3WkB4EKqA/uL0XOT04k8cweSnHvO5Qr1KtWbdWilPeqbhT2tvTnrGstns1KFxtHZL1q2qpUsHdqdRviqNR0/GhJ+bHA5PQia9ujtVrS8o1QcAkK48GQnSQfpx+glnd/e6vZU6tuqJVpVVYFHyQHIxkY8Oo8fKV909i1bu4RAraA+qofALqB59eGBHCT302/hUX6byStatR/a1qo5OfNvTkjsm/Nktxs65GMlKLV0PiGpDWMeuCPqZwh+s7xvreLbbNuWY8TbvRXqXqDQMfU5+k4ORgAdABHM+NsPe+DVPX1hT7CXiXj3iXlI+6hJmJkzE0sIQhACEIQBix6RCx6TGSxqS1a1nRg1JnRhwBRij+gZTmVklqxYCrTLfKGUt/CGGfaQzm+6T6Ct1/D2y94Se5oLrZmLMdCcSWPEnhzM+catVqjvUbialR2Y9SWyZ9Gbdps9pcogy721dVA5lmQgAT5ypU21KuMHXjB65lI+X/DVKauVPjK9/r7Hobb3fuLTQa6fBUXUr6tauPHDdRnkeM97s/3r/A1RRrYNvVKknHxUycLqB8RwGR5cJ1TbWw6d7aG2qYGUGhsZ0VAPhb349QSJwO/tKlCpUo1Rh6dRkZfMdOo8cxx4npwcq3tPHdu8vm5+1S7V9exn0Jt4d5ZXWj4i9rcaMcdWaZxj1nzvZMgdO9yaYZCyg4LAMNQB8xmbluVvHtJq9taJUJoI4zTNMEimOYL9AOX08p6O8PZlV71nsmQ0nYsKbNoennjp5YK9PH9ZnM4YKo2dXVYyK0t5Smp4arXTR8z0zvtsiwoYsAKjkcEpoUOrHOoxA+4yZzzblW8vS+0rlXNNnFNSAQlPgWCAeAxn+vObnsHsvfUHvWUAHPd0zkt6nHD6fcTobbHtjbmzNNO4Khe7xgYHLGORB45HI8YPIszDwq/0Ju1N/NU+nOHpLfl1b0jiO6m9dXZpdqaU6gqgBkJIGRyII+svK20Nv3CrVJFJMfAoK0aYJ4nHifUnPXA4b0OzKw1a81iM6tBZMenBc4+s2vZmy6FqgpW6LTUeAH69TBeXtbF3ndsUfqtRvNLTlPF6x4dZUp/OFWg6O9NlOVqMhBHIg4Im+7g7nPXdb28yUGkor8deMEZDfujhjrw8OfTK2wbOo/fVLei1QkEuaY1Fh4k+J84za90La2r11A/Y0ajgeBKqSB98Q9Vqc8vbleRbVq1TuurRuddeVPSevE+dKtLRVan+Ws6/ZiJ0vefcWteMl7aMmqrSo96rOUbWqgZU4xggDI4cvHPDmAcsxdiSzPqJPMknOZ9A2e3LKlRprUubZWWmgK98hYcOmcw3B9LbGResVWa7Wr+dcJ0+X7Gs7pdna0HFa90vUB1Cmo1JnwJJ5ny8PsZ7m8+5trtDS76qVVRpFSng5A5BlPMD6GKve0PZdLP7fvCP3aaMT9yAPea1tftYHy2VA5PKpXI/0qf6wfHpt7Tv31eSqVXV/KkuiXTsjXnLPS2b2YWtNg1ao9bTxxpCqfXHH3m526UaWi3Tu0+E6KQwCVXgcL0GRKe6u0mu7SjcVQFdl+IAYBI4ZA6Hn9Zx/bP/ALy7YszOl3cU1qO5ZwqVGChWJ4YGBw6TJg5027+beqov3HNE9qnhpwS7zqVxuPs2o/eNbqrE5IRmVc/wg4HoMS+wstnUi37G2pDP/Lk+Q5sfIcTOQHbt6F0rd3QH+PUJ/wAxJM8m4dqjd5VapUb/AOyo7VX+7EzJPV/xl+7CvXm6V2t+Sei/ND29996m2lUVUDJaUWLUlbg9SpjGth4cCcDzPXhrDxzxDyj7NmzRaoVFChIU8S8dUiHlI9CFNMTJmJpYQhCAEIQgE1j0ldY9JjRjQ5I5IhDGqZLObOx7hb2U7iklrXYLc0wEUk479RwBB8WxzHln09u63XsalXv3t6ZqZ1E6cBm6so4E+ZnCEM9FNr3QXQLm7Vfyrc1QuOgAbgJh8S9st77rsV7k8VqvVcuw7fU2rbU61Ozaqi1qgPd0S3xkD9PLPPBxynkbx7lWt9UFap3lOrgKz09I1qOWoEHJHX/xOP0lwSwPxE5LkkuTzyWPEmbHa747QprpWtqAGB3tMVSP5uBP1JmbxxWzL1hqvHuRVz5eUT6+fTpWwd2LWxH7BPiI4uxy58s9PLlPcmr7iXFxXtzcXVVqrVKjBcqqKiLwwFUAc9XHnynh75b6XNKu1hs6kWrKBqrFC+CRnCrjwyOPH08ZR89Yt+/kVUTvVL9zb005tvly+iOgVKiqCzkKo5sxAA+png7Q312ZQ4Pc0yf/AM81fdQROZjdPbO0CHumqceIavVKhc9APiHpgT1LTsmqHBrV0XqEU1Pc4MHsWDg2/wCvkp9lKb9Uqj173tYslBFGlcViOWQqKfrkn2ni3Pa3cH+4tqSf4rF/0Kz3LfsrtB/eVazeS6FHuDL1Hs22avzJUf8Aif8A7AQdqb2yLf8A0qq8Pu0vQ57ddou1any1adIdEpr+rDM8e92/f11KVrm4ZWGGplyFI6ECdpt9x9mpytlP8RZv1Mu0t27BOK2tuD17pc/fEQuh1W2MG3/TselKfpP1PnYI3RvsZYobMrP8lOq/8hb9BPoYrZUuf4enjroSV33k2bT/APlWgx4LXpk/YGHUdH/EVVS+Sz/6n6U+5xO03Sv6nyW1UeqFP9U2/d/sxqale+KhRx7pGySehPh9PvNvrb+bNXIFZqh6UqNRwfRtOPeeDtPtHcgrZW5BP/EuyAB6U0JJ+pEbxyr2htK+t2ij4a6w0/Op/RSbPt7a1DZdrqwuVUpb0RwNSoBhVAHhyyfATjCasFqh1O7NUqN1dyWY/cyxe3FWvVNe5qNVqngGbgqD8qqOCD0iGMhuTtg4axqXLmp8X7fnEW5iXjHMUxhH0UhbRVSMYxLGWWhbSu8c8Q5lI6IgZiEJpQQhCAEIQgGRGoYmMUwYyypjVMrqY5TIIZYUximV1MapmEMsqY1TEKY1TIIZ0Ls73hpU1azrMEy5ek7HCnVzQnwOeI65M3W7r21uDXrNRohiM1ahVMnGB8R5nkJwwGRWigOoKueukZlKo+TkbLou3HXvRPFRP56nU7vtEsl+GgK1wfzUqeinn+OoV4eYzPHr9ot02e5tqNPng1arVT5ZVVX7Z+s0wNJBpksujZmPRynvb9oNir75bTf/AItCl/h0OXoajNK1TeG/b5ryt/KKVP8A0oJ5AaS1TDusWxTwoXki019cN89zeNnwN3WA+wYCVatIP/ean/xHep/qJhqhqg6qmmngoF/hqQ5Ig9FX/tJZA5AD04TBaRJg6SzLGLYwJkGMAGMUxmWMUxgpEWMSxk2MUxlFpEGMSxjGMUxlFoWxiWMmxijKRaMQhCaUEIQgBCEIASSmRhAHqY5TKqmPUzGSywpjVMrqZNTJIZZUxqtKymMVpJDLKtJgyurRoaSQODSYaIDSYaDBoaZ1RWqGqDBuqGqK1Q1QCZaRJmC0gWg0mWi2MwWi2aDYMs0UxgzRbNKKSBjEsZlmkGM0sgxi2MkxiWMqCyDGQmSZiUWEIQgBCEIAQhCAEIQgGQYxTFSQMAsqZNTEK0YrSWQWFaNVpVVo0NMJgeGkw0rhpMNMJgsBpMNK4aSDTIJaLGqGqJ1Q1STIHaoaonVDVAgaWkC0WWmC02DUiZaQLSBaRLTUakZLRbNMFpFmmlAzRbGDNFM0pIpIwxi2MGMhKLCEIQaEIQgBCEIAQhCAEIQgBCEIBIGMVomZBgFlWjA0rBowNMgmCwGkg0QGkg0kmB4aTDSuGmQ0EwWNUNURqmdUyBA7VMaorVMaogQOLTBaL1SJaaIJlpEtFlpgtBsGS0iWkS0WWmooyzRbNAtISijMxCEGhCEIAQhCAEIQgF/uF6e5h3C9PcwhMOMsO4Xp7mHcL09zCECWHcL09zDuF6e5hCBLDuF6e5h3C9PcwhAlh3C9PcyYt06e5mIQJZIUF6e5kxRXp7mEJhkme5Xp7mZ7lenuYQmGSSFFenuZnuV6e5hCAY7lenuYdyvT3MIQJMdyvT3MwaK9PcwhAkwaK9PcyJor09zCE02WQNBenuZA0F6e5hCabLDuF6e5h3C9PcwhAlh3C9Pcw7henuYQgSw7henuYdwvT3MIQJYdwvT3MO4Xp7mEIEsO4Xp7mEIQJZ//2Q==" class="logo" style="width: 3rem; height:3rem; border-radius:50%"></a>
                <?php if ($isLoggedIn) : ?>
                    <h5 class="d-inline ps-1"> welcome, <?= $firstName . " " . $lastName ?></h5>
                <?php else : ?>
                    <h5 class="d-inline ps-1">RecipePoint</h5>
                <?php endif; ?>
            </nav>
            <nav class="navigation">
                <ul>
                    <!-- <li><a href="#">RECIPES</a></li>
                    <li><a href="#">CATEGORIES</a></li> -->
                </ul>
            </nav>
            <nav class="login-area">
                <div>
                    <?php if ($isLoggedIn) : ?>
                        <a href="./view/MyRecipes.php" class="btn btn-primary d-inline "><i class="bi bi-plus-lg pe-2"></i>MyRecipes</a>
                        <form method="post" class="d-inline">
                            <button type="submit" name="logout" class="btn btn-danger">Logout</button>
                        </form>
                    <?php else : ?>
                        <a href="./view/Login.php" class="btn btn-primary d-inline login">Login/SignUp</a>
                    <?php endif; ?>
                </div>
            </nav>
        </section>
    </header>

    <main>
        <section class="jumbo">
            <h1>what's cooking today?</h1>
            <div class="search">
                <form method="get" action="" class="d-flex w-100">
                    <input type="search" name="search" placeholder="find a recipe">
                    <button type="submit">FIND</button>
                </form>
            </div>
        </section>

        <section class="wrapper product">
            <h2 class="section-name">our delicious collections</h2>
            <!-- Recipes from database -->
            <?php foreach ($recipes as $recipe) : ?>
                <article class="card featured">
                    <?php if (isset($recipe['recipeImages']) && !empty($recipe['recipeImages'])) : ?>
                        <div id="carousel<?= $recipe['recipeId'] ?>" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">
                            <div class="carousel-inner">
                                <?php foreach (json_decode($recipe['recipeImages'], true) as $index => $image) : ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="<?= FETCH_SRC . htmlspecialchars($image) ?>" class="d-block w-100" alt="Recipe Image" style="height: 180px;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <!-- Carousel controls -->
                            <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $recipe['recipeId'] ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $recipe['recipeId'] ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <header class="card-content">
                        <span class="card-category Nonveg"><?= $recipe['foodCategory'] ?></span>
                        <span class="card-header"><?= $recipe['recipeName'] ?></span>
                        <span class="card-desc"><?= $recipe['recipeDescription'] ?></span>
                    </header>
                    <footer class="card-content">
                        <div class="contributor">
                            <a href="./view/RecipePage.php?recipeId=<?= $recipe['recipeId'] ?>"><span class="contributor-name">by <?= $recipe['creatorFullName'] ?></span></a>

                        </div>

                    </footer>
                </article>
            <?php endforeach; ?>

        </section>
    </main>

    <footer>
        <section class="wrapper">
            <nav>
            </nav>
        </section>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>