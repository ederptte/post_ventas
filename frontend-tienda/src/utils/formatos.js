const formatoMoneda = new Intl.NumberFormat('en-US', {
  style: 'currency',
  currency: 'USD',
  minimumFractionDigits: 0,
  maximumFractionDigits: 2,
})

const formatoFecha = new Intl.DateTimeFormat('es-ES', {
  day: '2-digit',
  month: '2-digit',
  year: 'numeric'
})

export const moneda = (valor) => formatoMoneda.format(Number(valor) || 0)

export const fecha = (valor) => {
  if (!valor) return 'Sin fecha'
  
  const fechaLimpia = valor.split(' ')[0]
  
  const fechaSegura = new Date(fechaLimpia.replace(/-/g, '/'))
  
  return formatoFecha.format(fechaSegura)
}

const UNIDADES_DECIMALES = new Set(['kilogramo'])

export const formatearCantidad = (valor, unidad) => {
  const n = Number(valor) || 0

  return UNIDADES_DECIMALES.has(unidad)
    ? n.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 3 })
    : Math.round(n).toLocaleString('es-CO')
}