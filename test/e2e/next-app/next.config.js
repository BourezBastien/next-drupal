/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  eslint: {
    // The root repo has a Jest-oriented Babel config that trips the default
    // Next.js lint parser in this workspace. Lint runs in CI separately.
    ignoreDuringBuilds: true,
  },
}

module.exports = nextConfig
