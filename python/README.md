# Tillo Technical Test
## Software Engineer - Python

**PLEASE READ THIS DOCUMENT FULLY**

We have supplied you with a barebones Flask api to get you started. Currently there is only one function, if you know an order's ID, you can pass it to the API to fetch details of the order. Using the provided [orders.json](./orders.json) file, please address the following requirements.

### Requirements

You should complete this task using __Python3__ and __Flask__. Should you choose to install additional pip packages please write a short comment to justify why. You can use the provided answers.md file for any supporting material.

Extend the API to allow users to filter all of the orders using the below GET parameters. It should be capable of handling multiple filters at a time.

| Parameter  | Type   | Description                                                   |
| :--------- | :----: | :------------------------------------------------------------ |
| currency   | string | Get all orders that were placed in a currency (GBP, USD, etc) |
| shipped_to | string | Get all orders that were shipped to the provided location     |
| cost       | float  | Get all orders that are greater than or equal to the cost     |

The API response should contain meta data about the filters used and the number of results returned, and should take the structure below.

```json
{
    "results": 3, // the number of filtered orders
    "filters": {}, // the filters that were used
    "orders": [
        // the filtered orders
    ]
}
```

### Bonus

- Feel free to add additional GET parameters that you think would be useful to filter the orders on and please document how these parameters can be tested.
- Dockerise this application.
- Demonstrate sensible logging.

### Testing

For testing your implementation we will be using `curl` commands such as.
```bash
curl localhost:5000/api/orders?currency=GBP&shipped_to=Essex&cost=150
```
