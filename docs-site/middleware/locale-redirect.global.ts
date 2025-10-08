export default defineNuxtRouteMiddleware((to) => {
  if (to.path === '/') {
    return navigateTo('/en', { redirectCode: 302 })
  }

  if (to.path === '/en/index') {
    return navigateTo('/en', { redirectCode: 302 })
  }

  if (to.path === '/fa/index') {
    return navigateTo('/fa', { redirectCode: 302 })
  }
})
