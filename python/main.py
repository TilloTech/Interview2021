from flask import Flask, jsonify, request

app = Flask(__name__)

@app.route("/api/orders", methods=['GET'])
def get_order_by_id():
    order_id = request.args.get("id")
    if valid_order_id(order_id) is not True:
        return jsonify({"error": f"{order_id} is incorrectly formatted"})
    return jsonify(next((order for order in data if order["id"] == order_id), {}))

def valid_order_id(order_id: str) -> bool:
    try:
        int(order_id, 16)
        return True
    except ValueError:
        return False

if __name__ == '__main__':
    import json
    with open('./orders.json') as json_file:
        data = json.load(json_file)
    
    app.run(debug=True)
