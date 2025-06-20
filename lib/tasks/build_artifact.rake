desc "Create a debian package from the binaries."
task :build_artifact do |task|

  calver_version = ENV['PIPELINE_VERSION'].nil? ? Time.now.strftime("%Y.%m.%d.%H%M%S") : ENV['PIPELINE_VERSION']
  git_short_ref  = `git rev-parse --short HEAD`.strip
  version        = ENV['ARTIFACT_VERSION'].nil? ? "#{calver_version}+sha.#{git_short_ref}" : ENV['ARTIFACT_VERSION']
  artifact_name  = 'projectaanvraag-api'
  vendor         = 'publiq VZW'
  maintainer     = 'Infra publiq <infra@publiq.be>'
  license        = 'Apache-2.0'
  description    = 'Projectaanvraag API'
  build_url      = ENV['JOB_DISPLAY_URL'].nil? ? '' : ENV['JOB_DISPLAY_URL']
  source         = 'https://github.com/cultuurnet/projectaanvraag-silex/'

  FileUtils.mkdir_p('pkg')
  FileUtils.mkdir_p('cache')
  FileUtils.mkdir_p('log')
  FileUtils.touch('config.yml')
  FileUtils.touch('user_roles.yml')
  FileUtils.touch('integration_types.yml')

  system("fpm -s dir -t deb -n #{artifact_name} -v #{version} -a all -p pkg \
    --prefix /var/www/projectaanvraag-api \
    -x '.git*' -x pkg -x '*.dist.yml' -x Jenkinsfile \
    --config-files /var/www/projectaanvraag-api/config.yml \
    --config-files /var/www/projectaanvraag-api/user_roles.yml \
    --config-files /var/www/projectaanvraag-api/integration_types.yml \
    --deb-user www-data --deb-group www-data \
    --description '#{description}' --url '#{source}' --vendor '#{vendor}' \
    --license '#{license}' -m '#{maintainer}' \
    --deb-field 'Pipeline-Version: #{calver_version}' \
    --deb-field 'Build-Url: #{build_url}' \
    --deb-field 'Git-Ref: #{git_short_ref}' \
    ."
  ) or exit 1
end
