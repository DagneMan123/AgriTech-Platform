export interface Product {
  id: number
  name: string
  description: string
  category: string
  price: number
  quantity: number
  unit: string
  image?: string
  farmer_id?: number
  farmer_name?: string
  supplier_id?: number
  status: 'active' | 'inactive' | 'archived'
  created_at: string
  updated_at: string
}

export interface CartItem {
  id: number
  product_id: number
  quantity: number
  product_name: string
  product_price: number
  subtotal: number
}

export interface Order {
  id: number
  order_number: string
  user_id: number
  status: 'pending' | 'confirmed' | 'shipped' | 'delivered' | 'cancelled'
  total_amount: number
  items_count: number
  created_at: string
  updated_at: string
}
