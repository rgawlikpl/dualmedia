# Opis

Styl kodowania ustawiony na zgodny z PSR-12

W przypadku poprawnego utworzenia zamówienia zwracany kod HTTP_OK (200) - chociaż w tym przypadku powinniśmy użyć 201 (CREATED)

W dalszej części opis procesu tworzenia prostego projektu początkowego z podstawowymi wymaganymi bundlami.

## Utworzenie projektu Symfony

`symfony new dualmedia --version="6.4.*"`

## ORM Pack

`composer require symfony/orm-pack`


## Maker Bundle

`composer require symfony/maker-bundle --dev`

## Encje

Tworze 3 encje:
- Product
    - id (autoincrement)
    - name (string 255, not null)
    - description (text, null)
    - price (decimal (10,2), not null)
- Order
    - id (autoincrement)
    - orderDate (datetime, not null)
    - orderProducts (ArrayCollection, OneToMany, related to OrderProduct)
- OrderProduct:
  - id (autoincrement)
  - order (int, not null)
  - product (int, not null)
  - quantity (int, not null)

## Event Listener

W celu dodania nagłówka do responsa stworzony `ResponseHeaderListener` dla eventu `kernel.response`

i dodana konfiguracja do `services.yaml`:

```
App\EventListener\ResponseHeaderListener:
        tags:
            - { name: kernel.event_listener, event: kernel.response, method: onKernelResponse }
```

## Baza danych

Konfigurujemy `.env` :
```
DATABASE_URL="mysql://root@127.0.0.1:3306/dualmedia?charset=utf8mb4"
```

## Kontrolery

Tworzymy kontrolery dla utworzonych encji:

`ProductController`

`OrderController`

# API endpoints

Do obsługi zamówień (i produktów)

## Product
- `/product/add`
```json
{
    "name": "Testowy produkt",
    "description": "Opis produktu",
    "price": 123.45
}
```

- `/product/get/{id}`


## Order

- `/order/add`
```json
{
    "products":
    [
        { 
            "id": 31,
            "quantity": 5
        },
        { 
            "id": 2,
            "quantity": 5
        }
    ]
}
```

- `/order/get/{id}`
przykładowy response:
```json
{
    "id": 20,
    "orderDate": "2024-05-21 09:01:13",
    "products": [
        {
            "productId": 1,
            "productName": "Test1",
            "description": "Opis 1",
            "quantity": 10,
            "basePrice": "338.00"
        },
        {
            "productId": 2,
            "productName": "Test 2",
            "description": "Opis 2",
            "quantity": 3,
            "basePrice": "11.34"
        }
    ],
    "prices": {
        "baseTotal": 3414.02,
        "discountTotal": 3072.62,
        "vatTotal": 4199.24,
        "itemCount": 2
    }
}
```

## PriceCalculatorCollector

W związku z ostatnim punktem został wykonany dodatkowy service `PriceCalculatorCollector`.

Został on zarejestrowany wraz z podkalkulatorami w `services.yaml`:
```
 App\Service\Pricing\VatPriceCalculator:
        arguments:
            $vatRate: 0.23 # stawka VAT 23%
        tags:
            - { name: 'app.price_calculator' }

    App\Service\Pricing\DiscountPriceCalculator:
        arguments:
            $discountRate: 0.1 # zniżka 10%
        tags:
            - { name: 'app.price_calculator' }

    # Rejestracja kolektora kalkulatorów
    App\Service\Pricing\PriceCalculatorCollector:
        arguments:
            $calculators: !tagged_iterator app.price_calculator
```

W jego skład wchodzą kalkulatory bazujące na interfejsie `PriceCalculatorInterface`:
- `VatPriceCalculator`
- `DiscountPriceCalculator`

Obliczanie cen wykonane jest w kontrolerze `OrderController` przy pomocy metody `prepareOrderDataResponse`, która jest wykorzystywana podczas tworzenia zamówienia oraz pobierania danych zamówienia.