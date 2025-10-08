export default defineNuxtRouteMiddleware((to) => {
  if (to.path === '/') {
    return navigateTo('/en/index', { redirectCode: 302 })
  }

  if (to.path === '/en') {
    return navigateTo('/en/index', { redirectCode: 302 })
  }

  if (to.path === '/fa') {
    return navigateTo('/fa/index', { redirectCode: 302 })
  }
})
