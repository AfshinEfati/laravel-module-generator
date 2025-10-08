export default defineNuxtRouteMiddleware((to) => {
  const config = useRuntimeConfig()
  const basePath = (config.public?.basePath as string) || '/'
  
  // Normalize base path
  const normalizedBase = basePath.endsWith('/') && basePath !== '/' 
    ? basePath.slice(0, -1) 
    : basePath
  
  // Strip base path from current path for comparison
  const stripBase = (path: string) => {
    if (normalizedBase === '/' || !path.startsWith(normalizedBase)) {
      return path
    }
    const stripped = path.slice(normalizedBase.length)
    return stripped.startsWith('/') ? stripped : `/${stripped}`
  }
  
  const relativePath = stripBase(to.path)
  
  if (relativePath === '/') {
    return navigateTo('/en', { redirectCode: 302 })
  }

  if (relativePath === '/en/index') {
    return navigateTo('/en', { redirectCode: 302 })
  }

  if (relativePath === '/fa/index') {
    return navigateTo('/fa', { redirectCode: 302 })
  }
})
