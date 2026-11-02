/**
 * Dynamic Agricultural Shipping & Logistics Calculator
 * Calculates freight & delivery costs based on product origin (e.g., Adama, Hawassa, Bahir Dar),
 * user destination, total weight (kg), distance (km), and product perishability.
 * 
 * Required Business Logic (Distance & Weight Matrix):
 * - For small/medium orders (weight <= 5 kg and short distance <= 30 km), keep standard flat rate (e.g. ETB 350).
 * - For bulk or heavier orders (weight > 5 kg or distance > 30 km):
 *   Formula: Base Fee + (Weight * Rate per kg) + (Distance * Rate per km)
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
  origin_location?: string
  product?: {
    category?: string
    name?: string
    unit?: string
    quantity_unit?: string
    origin_location?: string
  }
}

export interface RegionLogisticsInfo {
  region: string
  distanceKm: number
  multiplier: number
  transitDays: string
}

export interface ShippingBreakdown {
  baseFee: number
  weightKg: number
  ratePerKg: number
  weightCost: number
  originLocation: string
  destinationLocation: string
  distanceKm: number
  ratePerKm: number
  distanceCost: number
  isFlatRate: boolean
  flatRate: number
  totalFee: number
  formulaStr: string
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
  breakdown?: ShippingBreakdown
}

export const WEIGHT_FLAT_THRESHOLD_KG = 5.0
export const DISTANCE_SHORT_THRESHOLD_KM = 30.0

/**
 * Major Ethiopian Agricultural Hubs & Production Basins
 */
export const ETHIOPIAN_ORIGIN_HUBS: { name: string; region: string; distToAddis: number; label: string }[] = [
  { name: 'Adama', region: 'Oromia', distToAddis: 95, label: 'Adama (Oromia Agro-Hub)' },
  { name: 'Hawassa', region: 'Sidama', distToAddis: 275, label: 'Hawassa (Sidama Rift Valley)' },
  { name: 'Bahir Dar', region: 'Amhara', distToAddis: 495, label: 'Bahir Dar (Lake Tana Basin)' },
  { name: 'Addis Ababa', region: 'Addis Ababa', distToAddis: 25, label: 'Addis Ababa (Central Terminal)' },
  { name: 'Jimma', region: 'Oromia', distToAddis: 350, label: 'Jimma (Coffee & Agro Basin)' },
  { name: 'Gondar', region: 'Amhara', distToAddis: 660, label: 'Gondar (North Amhara Basin)' },
  { name: 'Dire Dawa', region: 'Dire Dawa', distToAddis: 450, label: 'Dire Dawa (Eastern Corridor)' },
  { name: 'Mekelle', region: 'Tigray', distToAddis: 780, label: 'Mekelle (Tigray Basin)' },
  { name: 'Shashamane', region: 'Oromia', distToAddis: 245, label: 'Shashamane (Southern Junction)' },
  { name: 'Nekemte', region: 'Oromia', distToAddis: 330, label: 'Nekemte (Western Grain Belt)' },
  { name: 'Bishoftu', region: 'Oromia', distToAddis: 45, label: 'Bishoftu (Central Agro Valley)' },
  { name: 'Arba Minch', region: 'SNNPR', distToAddis: 440, label: 'Arba Minch (Fruit Valley)' },
  { name: 'Dessie', region: 'Amhara', distToAddis: 385, label: 'Dessie (Eastern Amhara Basin)' },
  { name: 'Debre Markos', region: 'Amhara', distToAddis: 300, label: 'Debre Markos (Gojjam Basin)' }
]

export const DIRECT_DISTANCES: Record<string, number> = {
  'Adama:Addis Ababa': 95,
  'Addis Ababa:Adama': 95,
  'Adama:Hawassa': 185,
  'Hawassa:Adama': 185,
  'Adama:Dire Dawa': 355,
  'Dire Dawa:Adama': 355,
  'Hawassa:Addis Ababa': 275,
  'Addis Ababa:Hawassa': 275,
  'Hawassa:Shashamane': 25,
  'Shashamane:Hawassa': 25,
  'Bahir Dar:Addis Ababa': 495,
  'Addis Ababa:Bahir Dar': 495,
  'Bahir Dar:Gondar': 175,
  'Gondar:Bahir Dar': 175,
  'Bishoftu:Addis Ababa': 45,
  'Addis Ababa:Bishoftu': 45,
  'Bishoftu:Adama': 50,
  'Adama:Bishoftu': 50,
  'Jimma:Addis Ababa': 350,
  'Addis Ababa:Jimma': 350,
  'Dire Dawa:Harari': 55,
  'Harari:Dire Dawa': 55,
  'Dire Dawa:Addis Ababa': 450,
  'Addis Ababa:Dire Dawa': 450,
  'Mekelle:Addis Ababa': 780,
  'Addis Ababa:Mekelle': 780,
  'Arba Minch:Hawassa': 165,
  'Hawassa:Arba Minch': 165,
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

export const ETHIOPIAN_REGIONS = Object.keys(ETHIOPIAN_REGIONS_LOGISTICS)

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
 * Calculates dynamic distance (km) between product origin hub and buyer destination
 */
export function calculateOriginDestinationDistanceKm(origin?: string, destination?: string): number {
  const orig = String(origin || 'Addis Ababa').trim()
  const dest = String(destination || 'Addis Ababa').trim()

  if (orig.toLowerCase() === dest.toLowerCase()) {
    return 25
  }

  const directKey = `${orig}:${dest}`
  const reverseKey = `${dest}:${orig}`
  if (DIRECT_DISTANCES[directKey]) return DIRECT_DISTANCES[directKey]
  if (DIRECT_DISTANCES[reverseKey]) return DIRECT_DISTANCES[reverseKey]

  const origHub = ETHIOPIAN_ORIGIN_HUBS.find(h => h.name.toLowerCase() === orig.toLowerCase())
  const origDistToAddis = origHub ? origHub.distToAddis : 25
  const origRegion = origHub ? origHub.region : orig

  if (ETHIOPIAN_REGIONS_LOGISTICS[dest]) {
    const destRegionData = ETHIOPIAN_REGIONS_LOGISTICS[dest]
    const destDistToAddis = destRegionData.distanceKm

    if (origRegion.toLowerCase() === dest.toLowerCase()) {
      return Math.min(60, Math.round(destDistToAddis * 0.4))
    }
    if (dest.toLowerCase() === 'addis ababa') {
      return Math.max(25, origDistToAddis)
    }
    if (orig.toLowerCase() === 'addis ababa') {
      return Math.max(25, destDistToAddis)
    }
    return Math.round(origDistToAddis + (destDistToAddis * 0.85))
  }

  const destHub = ETHIOPIAN_ORIGIN_HUBS.find(h => h.name.toLowerCase() === dest.toLowerCase())
  if (destHub) {
    if (origRegion.toLowerCase() === destHub.region.toLowerCase()) {
      return 40
    }
    return Math.round(origDistToAddis + destHub.distToAddis)
  }

  return 95
}

/**
 * Resolves effective origin location and distance from cart items to destination
 */
export function resolveCartOriginAndDistance(cartItems: any[], destination = 'Addis Ababa'): {
  origin: string
  allOrigins: string[]
  distanceKm: number
  destination: string
} {
  let maxDistance = 25
  let primaryOrigin = 'Addis Ababa'
  const originsFound: string[] = []

  if (Array.isArray(cartItems)) {
    for (const item of cartItems) {
      const orig = item?.origin_location || item?.product?.origin_location || 'Addis Ababa'
      originsFound.push(orig)
      const dist = calculateOriginDestinationDistanceKm(orig, destination)
      if (dist >= maxDistance) {
        maxDistance = dist
        primaryOrigin = orig
      }
    }
  }

  return {
    origin: primaryOrigin,
    allOrigins: Array.from(new Set(originsFound)),
    distanceKm: maxDistance,
    destination
  }
}

/**
 * Checks if a cart item is perishable (requires temperature control)
 */
export function isItemPerishable(item: any): boolean {
  if (!item) return false
  const cat = String(item.product?.category || item.category || '').toLowerCase().trim()
  const name = String(item.product_name || item.product?.name || item.name || '').toLowerCase()

  if (PERISHABLE_CATEGORIES.some(c => cat.includes(c))) return true

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
  const rawUnit = String(item.unit || item.product?.unit || item.product?.quantity_unit || 'kg').toLowerCase().trim()
  const qty = Number(item.quantity) || 1

  const unit = rawUnit.replace(/^(etb|birr|\$)?\s*\/?\s*/i, '').trim()

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
    case 'bundle':
    case 'bundles':
      return qty * 5
    case 'piece':
    case 'pieces':
      return qty * 2
    case 'liter':
    case 'liters':
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
 * Matrix calculation helper for a single shipping tier
 */
function calculateTierFee(
  totalWeight: number,
  distance: number,
  origin: string,
  destination: string,
  config: {
    flatRate: number
    baseFee: number
    ratePerKg: number
    ratePerKm: number
  }
): { fee: number; breakdown: ShippingBreakdown } {
  const isSmallLocal = totalWeight <= WEIGHT_FLAT_THRESHOLD_KG && distance <= DISTANCE_SHORT_THRESHOLD_KM

  if (isSmallLocal) {
    return {
      fee: config.flatRate,
      breakdown: {
        baseFee: config.flatRate,
        weightKg: totalWeight,
        ratePerKg: config.ratePerKg,
        weightCost: 0,
        originLocation: origin,
        destinationLocation: destination,
        distanceKm: distance,
        ratePerKm: config.ratePerKm,
        distanceCost: 0,
        isFlatRate: true,
        flatRate: config.flatRate,
        totalFee: config.flatRate,
        formulaStr: `Flat Rate to ${destination} (${distance}km): ETB ${config.flatRate} (Orders ≤ ${WEIGHT_FLAT_THRESHOLD_KG}kg & ≤ ${DISTANCE_SHORT_THRESHOLD_KM}km)`
      }
    }
  }

  // Weight and Distance Matrix Formula: Base Fee + (Weight * Rate per kg) + (Distance * Rate per km)
  const weightCost = Math.round(totalWeight * config.ratePerKg)
  const distanceCost = Math.round(distance * config.ratePerKm)
  const rawTotal = config.baseFee + weightCost + distanceCost
  const finalFee = Math.max(config.flatRate, Math.round(rawTotal / 10) * 10)

  return {
    fee: finalFee,
    breakdown: {
      baseFee: config.baseFee,
      weightKg: totalWeight,
      ratePerKg: config.ratePerKg,
      weightCost,
      originLocation: origin,
      destinationLocation: destination,
      distanceKm: distance,
      ratePerKm: config.ratePerKm,
      distanceCost,
      isFlatRate: false,
      flatRate: config.flatRate,
      totalFee: finalFee,
      formulaStr: `Base ETB ${config.baseFee} + (${totalWeight}kg × ETB ${config.ratePerKg}) + (${distance}km to ${destination} × ETB ${config.ratePerKm}) = ETB ${finalFee}`
    }
  }
}

/**
 * Calculates dynamic shipping fees and filters eligible options
 */
export function getDynamicShippingOptions(
  cartItems: any[],
  destinationName = 'Addis Ababa',
  forcedOrigin?: string
): DynamicShippingOption[] {
  const totalWeight = Math.max(1, getCartTotalWeightKg(cartItems))
  const hasPerishables = cartHasPerishables(cartItems)

  const originInfo = forcedOrigin
    ? {
        origin: forcedOrigin,
        allOrigins: [forcedOrigin],
        distanceKm: calculateOriginDestinationDistanceKm(forcedOrigin, destinationName),
        destination: destinationName
      }
    : resolveCartOriginAndDistance(cartItems, destinationName)

  const distance = originInfo.distanceKm
  const origin = originInfo.origin
  const regionInfo = ETHIOPIAN_REGIONS_LOGISTICS[destinationName] || ETHIOPIAN_REGIONS_LOGISTICS['Addis Ababa']

  // 1. STANDARD FREIGHT
  // Base: 200 ETB, Rate/kg: 10 ETB, Rate/km: 6 ETB, Flat Rate: 350 ETB
  const stdCalc = calculateTierFee(totalWeight, distance, origin, destinationName, {
    flatRate: 350,
    baseFee: 200,
    ratePerKg: 10,
    ratePerKm: 6
  })

  // 2. EXPRESS DIRECT LOGISTICS
  // Base: 350 ETB, Rate/kg: 16 ETB, Rate/km: 10 ETB, Flat Rate: 650 ETB
  const expCalc = calculateTierFee(totalWeight, distance, origin, destinationName, {
    flatRate: 650,
    baseFee: 350,
    ratePerKg: 16,
    ratePerKm: 10
  })

  const options: DynamicShippingOption[] = [
    {
      key: 'standard',
      title: 'Standard Agricultural Freight',
      desc: stdCalc.breakdown.isFlatRate
        ? `Ambient cargo dispatch • ${regionInfo.transitDays} (Flat standard rate: ${origin} → ${destinationName})`
        : `Ambient cargo dispatch • ${regionInfo.transitDays} (${origin} → ${destinationName} • ${totalWeight} kg • ${distance} km)`,
      fee: stdCalc.fee,
      badge: stdCalc.breakdown.isFlatRate ? 'STANDARD FLAT RATE' : 'DYNAMIC BULK MATRIX',
      isPerishableOnly: false,
      transitDays: regionInfo.transitDays,
      estimatedDays: '3 - 5 business days',
      breakdown: stdCalc.breakdown
    },
    {
      key: 'express',
      title: 'Express Direct Dispatch',
      desc: `Priority dedicated transit (${origin} → ${destinationName}) • 1 - 2 business days`,
      fee: expCalc.fee,
      badge: hasPerishables ? 'FASTER TRANSIT' : null,
      isPerishableOnly: false,
      transitDays: '1 - 2 business days',
      estimatedDays: '1 - 2 business days',
      breakdown: expCalc.breakdown
    }
  ]

  // 3. COLD-CHAIN AGRO TRANSPORT
  // DYNAMIC FILTERING: ONLY included if cart has perishable produce!
  if (hasPerishables) {
    const coldCalc = calculateTierFee(totalWeight, distance, origin, destinationName, {
      flatRate: 1200,
      baseFee: 700,
      ratePerKg: 22,
      ratePerKm: 15
    })

    options.push({
      key: 'overnight',
      title: 'Cold-Chain Agro Transport',
      desc: `Climate-controlled (+4°C) from ${origin} to ${destinationName} • Guaranteed crisp freshness`,
      fee: coldCalc.fee,
      badge: 'RECOMMENDED FOR FRESH PRODUCE',
      isPerishableOnly: true,
      requiresColdChain: true,
      transitDays: 'Guaranteed next-day cold delivery',
      estimatedDays: 'Next-day delivery',
      breakdown: coldCalc.breakdown
    })
  }

  return options
}
