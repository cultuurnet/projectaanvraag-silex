namespace 'projectaanvraag-api' do
  desc "Build binaries"
  task :build do |task|
    system('composer install --no-dev --ignore-platform-reqs --optimize-autoloader --no-interaction') or exit 1
    system('touch config.yml user_roles.yml integration_types.yml') or exit 1
  end
end
