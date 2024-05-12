<?php
$products = file_get_contents('products.json');
$products = json_decode($products, false);
$cart = [];

function displayProducts(array $products): string
{
    $listOfItems = '';
    $index = 0;
    foreach ($products as $product) {
        $index++;
        $listOfItems = $listOfItems . "$index. " . $product->name . " - €" . $product->price . "\n";
    }
    return $listOfItems;
}

function displayUserMenu(array $cart): string
{
    $userMenu = "\nWould you like to:\n" .
        "1 - Add to the cart.\n" .
        "2 - View items in the cart.\n" .
        "3 - Exit.\n";
    if (count($cart) > 0) {
        $userMenu = "\nWould you like to:\n" .
            "1 - Add to the cart.\n" .
            "2 - View items in the cart. (" .
            count($cart) .
            ")\n3 - Exit.\n";
    }
    return $userMenu;
}
echo "Welcome to Narvesen! Here's what we have today:\n";
do {
    echo displayProducts($products) . displayUserMenu($cart);
    $userMenuChoice = (int) readline("Enter number (1-3): ");
    if ($userMenuChoice > 3 || $userMenuChoice < 1) {
        continue;
    }
    switch ($userMenuChoice) {
        case 1: //Add to the cart
            do{
                echo PHP_EOL . displayProducts($products) . "\nEnter 0 to cancel.\n";
                $userProductChoice = (int) readline("Enter the product number (1-" .
                    count($products) . "): ");
            } while ($userProductChoice > count($products) || $userProductChoice < 0);
            if ($userProductChoice === 0) {
                break;
            } elseif ($products[$userProductChoice-1]->inStock === 0) {
                echo "\nSorry this product is out of stock.\n";
                readline("Press any key to continue...");
                break;
            }
            do{
                echo PHP_EOL .
                     $products[$userProductChoice-1]->name .
                     " - Price €" .
                     $products[$userProductChoice-1]->price .
                     "\nAvailable in stock: " .
                     $products[$userProductChoice-1]->inStock .
                     "\n\nEnter 0 to cancel.\n";
                $userAmountChoice = (int) readline("Enter the amount you want to buy (1-" .
                                                $products[$userProductChoice-1]->inStock . "): ");
                if ($userAmountChoice < 0 || $userAmountChoice > $products[$userProductChoice-1]->inStock) {
                    echo displayProducts($products) . "\nThe amount has to be at least 1 or less than " .
                        ($products[$userProductChoice-1]->inStock +1) . ".\n";
                    readline("Press any key to continue...");
                }
            } while ($userAmountChoice < 0 || $userAmountChoice > $products[$userProductChoice-1]->inStock);
            if ($userAmountChoice !== 0) {
                if (count($cart) > 0) {
                    for ($i = 0; $i < count($cart); $i++){
                        if ($cart[$i]->name === $products[$userProductChoice-1]->name) {
                            $cart[array_search($products[$userProductChoice-1]->name, $cart)]->amount += $userAmountChoice;
                            break;
                        }
                        if ($i === count($cart)-1){
                            $item = new stdClass();
                            $item -> name = $products[$userProductChoice-1]-> name;
                            $item -> amount = $userAmountChoice;
                            $cart[] = $item;
                        }
                    }
                } else {
                    $item = new stdClass();
                    $item -> name = $products[$userProductChoice-1]-> name;
                    $item -> amount = $userAmountChoice;
                    $cart[] = $item;
                }
                $products[$userProductChoice-1]->inStock -= $userAmountChoice;
            }
            break;
        case 2: //View items in the cart.
            $totalCost = 0;
            $totalAmount = 0;
            echo "\nItems in cart:\n";
            foreach ($cart as $item) {
                $totalAmount += $item->amount;
                $totalCost += $item->amount * $products[array_search($item->name, $products)]->price;
                echo "$item->name x $item->amount - Cost: €" .
                    ($item->amount * $products[array_search($item->name, $products)]->price) . "\n";
            }
            echo "\nItems total: " . count($cart) . "\nTotal cost: €$totalCost \nTotal amount: $totalAmount\n";
            readline("Press any key to continue...");
            if (count($cart) > 0) {
                echo "\nWould you like to proceed to checkout?\n";
                $checkout = (int) readline("(1 - yes | 0 - no): ");
                if ($checkout === 1) {
                    echo "\nItems purchased. Thank you! Feel free to shop again.\n";
                    exit;
                }
                break;
            }
            break;
        case 3:
            exit;
        default:
            echo displayProducts($products) . displayUserMenu($cart) . "Please enter a number (1-3).\n";
    }
} while ($userMenuChoice !== 4);
