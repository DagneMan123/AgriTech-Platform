/**
 * Dynamic Agricultural Shipping Calculator
 * Calculates freight & delivery costs based on total weight, volume, distance,
 * and dynamically filters shipping options based on product perishability.
 */

export interface CartItem {
  id: number
  product_id?: number
  product_name?: string
  quantity: number
  unit?: string
  unit_price?: number
  price?: number
  subtotal?: number
  category?: string
  product?: {
    category?: string
    name?: string
    unit?: string
    quantity_unit?: string
  }
}

export interface RegionLogisticsInfo {
  region: string
  distanceKm: number
  multiplier: number
  transitDays: string
}

export interface DynamicShippingOption {
  key: 'standard' | 'express' | 'overnight' | 'cold_chain'
  title: string
  desc: string
  fee: number
  badge: string | null
  isPerishableOnly: boolean
  transitDays: string
  requiresColdChain?: boolean
  estimatedDays: string
}

export const ETHIOPIAN_REGIONS_LOGISTICS: Record<string, RegionLogisticsInfo> = {
  'Addis Ababa': {
    region: 'Addis Ababa',
    distanceKm: 25,
    multiplier: 1.0,
    transitDays: 'Same-day or next-day delivery'
  },
  'Oromia': {
    region: 'Oromia',
    distanceKm: 120,
    multiplier: 1.25,
    transitDays: '1 - 2 business days'
  },
  'Amhara': {
    region: 'Amhara',
    distanceKm: 450,
    multiplier: 1.55,
    transitDays: '2 - 3 business days'
  },
  'Sidama': {
    region: 'Sidama',
    distanceKm: 275,
    multiplier: 1.35,
    transitDays: '1 - 2 business days'
  },
  'SNNPR': {
    region: 'SNNPR',
    distanceKm: 380,
    multiplier: 1.45,
    transitDays: '2 - 3 business days'
  },
  'Dire Dawa': {
    region: 'Dire Dawa',
    distanceKm: 480,
    multiplier: 1.6,
    transitDays: '2 - 3 business days'
  },
  'Harari': {
    region: 'Harari',
    distanceKm: 510,
    multiplier: 1.65,
    transitDays: '2 - 4 business days'
  },
  'Afar': {
    region: 'Afar',
    distanceKm: 580,
    multiplier: 1.75,
    transitDays: '3 - 4 business days'
  },
  'Benishangul-Gumuz': {
    region: 'Benishangul-Gumuz',
    distanceKm: 620,
    multiplier: 1.8,
    transitDays: '3 - 5 business days'
  },
  'Tigray': {
    region: 'Tigray',
    distanceKm: 780,
    multiplier: 1.95,
    transitDays: '3 - 5 business days'
  },
  'Somali': {
    region: 'Somali',
    distanceKm: 630,
    multiplier: 1.9,
    transitDays: '3 - 5 business days'
  },
  'Gambela': {
    region: 'Gambela',
    distanceKm: 770,
    multiplier: 1.95,
    transitDays: '4 - 5 business days'
  }
}

export const PERISHABLE_CATEGORIES = [
  'vegetables',
  'vegetable',
  'fruits',
  'fruit',
  'dairy',
  'milk',
  'meat',
  'poultry',
  'fish',
  'fresh produce',
  'horticulture',
  'flowers'
]

export const NON_PERISHABLE_CATEGORIES = [
  'grains',
  'grain',
  'cereals',
  'cereal',
  'pulses',
  'pulse',
  'legumes',
  'seeds',
  'seed',
  'coffee',
  'spices',
  'spice',
  'oilseeds',
  'dry goods',
  'flour'
]

/**
 * Checks if a cart item is perishable (requires temperature control)
 */
export function isItemPerishable(item: any): boolean {
  if (!item) return false
  const cat = String(item.product?.category || item.category || '').toLowerCase().trim()
  const name = String(item.product_name || item.product?.name || item.name || '').toLowerCase()

  if (PERISHABLE_CATEGORIES.some(c => cat.includes(c))) return true

  // Fallback to name keywords
  const perishableKeywords = [
    'tomato', 'onion', 'potato', 'cabbage', 'carrot', 'pepper', 'spinach',
    'lettuce', 'banana', 'mango', 'avocado', 'apple', 'orange', 'milk',
    'cheese', 'egg', 'meat', 'beef', 'chicken', 'fish', 'strawberry'
  ]
  return perishableKeywords.some(k => name.includes(k))
}

/**
 * Calculate normalized weight in kilograms for a single item
 */
export function getItemWeightKg(item: any): number {
  if (!item) return 0
  const unit = String(item.unit || item.product?.unit || item.product?.quantity_unit || 'kg').toLowerCase().trim()
  const qty = Number(item.quantity) || 1

  switch (unit) {
    case 'kg':
    case 'kilogram':
    case 'kilograms':
      return qty
    case 'g':
    case 'gram':
    case 'grams':
      return qty * 0.001
    case 'quintal':
    case 'quintals':
    case 'qtl':
      return qty * 100
    case 'ton':
    case 'tonne':
    case 'tons':
      return qty * 1000
    case 'crate':
    case 'crates':
      return qty * 25
    case 'bag':
    case 'bags':
    case 'sack':
    case 'sacks':
      return qty * 50
    case 'liter':
    case 'liters':
    case 'l':
      return qty * 1
    default:
      return qty * 1
  }
}

/**
 * Total cart weight in kilograms
 */
export function getCartTotalWeightKg(cartItems: any[]): number {
  if (!Array.isArray(cartItems) || cartItems.length === 0) return 0
  return cartItems.reduce((sum, item) => sum + getItemWeightKg(item), 0)
}

/**
 * Check if the cart contains any perishable items
 */
export function cartHasPerishables(cartItems: any[]): boolean {
  if (!Array.isArray(cartItems) || cartItems.length === 0) return false
  return cartItems.some(item => isItemPerishable(item))
}

/**
 * Calculates dynamic shipping fees and filters eligible options
 */
export function getDynamicShippingOptions(
  cartItems: any[],
  regionName = 'Addis Ababa'
): DynamicShippingOption[] {
  const totalWeight = Math.max(1, getCartTotalWeightKg(cartItems))
  const hasPerishables = cartHasPerishables(cartItems)

  const regionInfo = ETHIOPIAN_REGIONS_LOGISTICS[regionName] || ETHIOPIAN_REGIONS_LOGISTICS['Addis Ababa']
  const distance = regionInfo.distanceKm
  const mult = regionInfo.multiplier

  // 1. STANDARD FREIGHT
  // Base: 250 ETB, + distance rate + tiered weight rate
  const stdBase = 250
  const stdDist = distance * 0.50
  let stdWeight = 0
  if (totalWeight > 10) {
    const billableWeight = totalWeight - 10
    if (billableWeight <= 90) {
      stdWeight = billableWeight * 4.0 // 4 ETB/kg for next 90kg
    } else if (billableWeight <= 490) {
      stdWeight = (90 * 4.0) + ((billableWeight - 90) * 2.5) // Bulk tier 1
    } else {
      stdWeight = (90 * 4.0) + (400 * 2.5) + ((billableWeight - 490) * 1.8) // Heavy agricultural bulk
    }
  }
  const standardFee = Math.max(350, Math.round(((stdBase + stdDist + stdWeight) * mult) / 10) * 10)

  // 2. EXPRESS DIRECT LOGISTICS
  // Base: 550 ETB, + priority distance rate + priority weight rate
  const expBase = 550
  const expDist = distance * 0.90
  let expWeight = 0
  if (totalWeight > 10) {
    const billableWeight = totalWeight - 10
    if (billableWeight <= 90) {
      expWeight = billableWeight * 7.0
    } else {
      expWeight = (90 * 7.0) + ((billableWeight - 90) * 5.0)
    }
  }
  const expressFee = Math.max(750, Math.round(((expBase + expDist + expWeight) * mult) / 10) * 10)

  const options: DynamicShippingOption[] = [
    {
      key: 'standard',
      title: 'Standard Agricultural Freight',
      desc: `Ambient cargo dispatch • ${regionInfo.transitDays}`,
      fee: standardFee,
      badge: null,
      isPerishableOnly: false,
      transitDays: regionInfo.transitDays,
      estimatedDays: '3 - 5 business days'
    },
    {
      key: 'express',
      title: 'Express Direct Dispatch',
      desc: `Priority dedicated farm-to-door transit • 1 - 2 business days`,
      fee: expressFee,
      badge: hasPerishables ? 'FASTER TRANSIT' : null,
      isPerishableOnly: false,
      transitDays: '1 - 2 business days',
      estimatedDays: '1 - 2 business days'
    }
  ]

  // 3. COLD-CHAIN AGRO TRANSPORT
  // DYNAMIC FILTERING: ONLY included if cart has perishable produce!
  // For non-perishable goods (e.g. grains, pulses, seeds), this option is hidden.
  if (hasPerishables) {
    const coldBase = 1200 // Refrigeration compressor overhead
    const coldDist = distance * 1.60
    let coldWeight = 0
    if (totalWeight > 10) {
      const billableWeight = totalWeight - 10
      if (billableWeight <= 90) {
        coldWeight = billableWeight * 10.0
      } else {
        coldWeight = (90 * 10.0) + ((billableWeight - 90) * 8.0)
      }
    }
    const coldChainFee = Math.max(1800, Math.round(((coldBase + coldDist + coldWeight) * mult) / 10) * 10)

    options.push({
      key: 'overnight', // mapped to cold_chain/overnight key
      title: 'Cold-Chain Agro Transport',
      desc: `Climate-controlled (+4°C) for fresh produce • Guaranteed crisp freshness`,
      fee: coldChainFee,
      badge: 'RECOMMENDED FOR FRESH PRODUCE',
      isPerishableOnly: true,
      requiresColdChain: true,
      transitDays: 'Guaranteed next-day cold delivery',
      estimatedDays: 'Next-day delivery'
    })
  }

  return options
}
