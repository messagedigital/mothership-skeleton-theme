# Load DSL and Setup Up Stages
require 'capistrano/setup'

# Includes default deployment tasks
require 'capistrano/deploy'

# Includes the git tasks
require "capistrano/git"

# Includes the default configuration
require "capistrano/defaults"

# Includes steps logging gem
require "steps"

# Overwrite the current_path method to ensure we can set it in each environment
module Capistrano
	module DSL
		module Paths
			def current_path
				fetch(:current_path, deploy_path.join("current"))
			end
		end
	end
end

# Loads in tasks from the composer vendor package
Dir.glob('vendor/message/deployment/tasks/*.cap').each { |r| import r }


# --------------------------------------------------------------------------
#  MOTHERSHIP DEPLOYMENT
# --------------------------------------------------------------------------
#
# !!! MOVE ALL THIS TO THE DEPLOYMENT REPO ON GITHUB !!!
#
# Further reading:
# http://www.capistranorb.com
# https://github.com/capistrano/capistrano/wiki/Capistrano-Tasks
# http://www.davegardner.me.uk/blog/2012/02/13/php-deployment-with-capistrano/
# https://help.github.com/articles/deploying-with-capistrano
# http://jondavidjohn.com/blog/2012/04/cleaning-up-capistrano-deployment-output
#
# USEFUL COMMAND - `cap -vT`
#
# @author Laurence Roberts <lsjroberts@gmail.com>
#

# --------------------------------------------------------------------------
#  Configuration
# --------------------------------------------------------------------------
#
# Configure your application.
#

# Application
organisation = "messagedigital"
application  = "rsar"
domain       = "rsar.message.co.uk"
user         = "root"

# Stage
set :stage, "unspecified"

# Repository
set :scm, "git"
set :repo_url, "git@github.com:#{organisation}/#{application}.git"
master = "master"

# Releases
set :keep_releases, 5

set :format, :pretty
set :log_level, :important

# Default Directory Permissions (these can be extended by listening to 'cog.deploy.permissions')
set :permissions, {
	"bin"             => 744,
	"tmp"             => 777,
	"logs"            => 777,
	"data"			  => 777,
	"public/assets"   => 777,
	"public/barcodes" => 777,
	"public/cogules"  => 777,
	"public/files"    => 777,
	"public/resize"   => 777
}


# --------------------------------------------------------------------------
#  Environments
# --------------------------------------------------------------------------
#
# You can set up additional environments for your local environment or for
# extra staging and production servers.
#

# ------------
# Server
# ------------
#
# These server tasks can be reused to handle multiple environments on a
# single server.
#

namespace :server do
	task :init do
		# Initialise the environment
		role :all,          "#{user}@#{domain}"

		if not fetch(:deploy_via)
			set :deploy_via, :clone
		end

		if "master" == "#{fetch(:branch)}"
			set :branch, "#{master}"
		end

		# Staging settings
		if "unspecified" == "#{fetch(:stage)}"
			set :stage, :staging
		end

		if not fetch(:env)
			set :env, "live"
		end

		if not fetch(:current_path)
			set :current_path, "#{deploy_to}/staging"
		end

		# Live settings
		if not fetch(:live_path)
			set :live_path, "#{deploy_to}/live"
		end
	end

	task :complete do
		on roles :all do
			# Get the real path the staging symlink points to
			real_staging_path = getSymlinkTarget(current_path)

			# Get the real path the live symlink points to
			real_live_path = getSymlinkTarget(fetch(:live_path))

			# Tell the user the two paths
			info "Current Staging Release: #{real_staging_path}"
			info "Current Live Release:    #{real_live_path}"

			# TODO: add info output git commit hashes for staging and live

			# If the real live and staging paths match, skip to the next role
			if real_live_path == real_staging_path
				error "The live symlink already matches the staging symlink"

				# TODO: this should call 'next' if there are multiple roles,
				# however this does not work as expected here so 'exit' is used.

				# next

				exit
			end
		end

		confirm "Are you sure you wish to complete the deployment and switch the staged release to live?", :vital => true

		# Re-set the stage to live
		set :stage, :live

		# Run the pre-release tasks, these are also run after the deploy but
		# duplication here just acts as a safety net
		invoke "phpunit:run"
		invoke "filesystem:cache:clear"
		invoke "filesystem:cache:warm"
		invoke "filesystem:create_data_folders"
		invoke "filesystem:fix_permissions"

		fireCogCommand("#{fetch(:live_path)}", "deploy:event --env=#{fetch(:env)} --task=complete --before")

		on roles :all do
			# Symlink the live path to the real staging path
			createSymlink(fetch(:live_path), getSymlinkTarget(current_path))
		end

		# fireCogCommand("#{fetch(:live_path)}", "deploy:event --env=#{fetch(:env)} --task=complete --after")

		invoke "git:tag_release"

		# Take the holding page down, if it is not up this will just ensure
		# the live symlink points to the public directory
		invoke "holding:down"
	end

	task :revert do
		# Re-initialise settings to the live environment
		set :stage, :live
		set :current_path, "#{fetch(:live_path)}"

		confirm "Are you sure you wish to revert the live release to the previous version?", :vital => true

		invoke "deploy:rollback"
	end
end


# ------------
# Production
# ------------
#
# The initial `cap production deploy` command deploys the code to the staging
# environment. The code is switched to the production environment by using the
# command `cap production complete`.
#

task :production do
	set  :deploy_to, "/var/www/vhosts/realstarsarerare.com/production"

	invoke "server:init"

	task :complete do
		invoke "server:complete"
	end

	task :revert do
		invoke "server:revert"
	end
end


# ------------
# Dev
# ------------
#
# The dev environments are used to test development branches.
#

# task :dev1 do
# 	set  :deploy_to, "/var/www/vhosts/unionmusicstore.com/dev1"
# 	set  :branch,    "dev1"
# 	set  :env,       "dev-dev1"

# 	invoke "server:init"

# 	task :complete do
# 		invoke "server:complete"
# 	end

# 	task :revert do
# 		invoke "server:revert"
# 	end
# end

# task :dev2 do
# 	set  :deploy_to, "/var/www/uniform_wares_dev2"
# 	set  :branch,    "dev2"
# 	set  :env,       "dev-dev2"

# 	invoke "server:init"


# 	task :complete do
# 		invoke "server:complete"
# 	end

# 	task :revert do
# 		invoke "server:revert"
# 	end
# end


# ------------
# Local
# ------------
#
# For testing the deployment process on your local machine.
#

task :local do

end


# --------------------------------------------------------------------------
#  Deploy Tasks
# --------------------------------------------------------------------------
#
# These tasks handle the deployment process.
#

before :deploy, :confirm_holding do
	# This is a little bit odd logic to get around issues of invoke commands
	# inside the 'on roles :all do' block.

	holding = false
	on roles :all do
		if "" != getSymlinkTarget(fetch(:live_path))
			if "staging" == "#{fetch(:stage)}"
				holding = confirm "Do you wish to put up the holding page?"
			end
		end
	end
	if holding
		invoke "holding:up"
	end
end

after :deploy, :filesystem_tasks do
	invoke "filesystem:cache:clear"
	invoke "filesystem:cache:warm"
	invoke "filesystem:create_data_folders"
	invoke "filesystem:fix_permissions"
end

after :deploy, :copy_shared_files do
	copyfiles = false
	on roles :all do
		if "staging" == "#{fetch(:stage)}"
			within deploy_to do
				if test("[ -d #{fetch(:deploy_to)}/shared_data/live ]")
					if confirm "Do you wish to copy the live shared data files to the #{fetch(:stage)} environment?"
						execute :rm, "-rf", "shared_data/#{fetch(:stage)}"
						execute :cp, "-r", "shared_data/live", "shared_data/#{fetch(:stage)}"
					end
				else
					info "No live shared data files available to copy at: #{fetch(:deploy_to)}/shared_data/live"
				end
			end
		end
	end
end

before "deploy:published", "composer:install"

namespace :deploy do
	task :restart do
		# run "sudo apachectl restart"
	end
end


# --------------------------------------------------------------------------
#  Cog Deployment Event Dispatchers
# --------------------------------------------------------------------------
#
# These tasks allow cog to listen to deployment events and run custom
# commands. If required you can add additional events here.
#

after "deploy", :cog_after_deploy do
	# fireCogCommand("#{current_path}", "deploy:event --env=#{fetch(:stage)} --task=deploy --after")
	fireCogCommand("#{fetch(:current_path)}", "asset:dump --env=#{fetch(:env)}")
	fireCogCommand("#{fetch(:current_path)}", "asset:generate --env=#{fetch(:env)}")
	fireCogCommand("#{fetch(:current_path)}", "migrate:install --env=#{fetch(:env)}")
	fireCogCommand("#{fetch(:current_path)}", "migrate:run --env=#{fetch(:env)}")
end

def fireCogCommand(path, command)
	on roles :all do
		if test("[ -d #{path} ]")
			info "Firing Cog Command: #{command} in #{path}"
			# within path do
				begin
					execute "#{path}/bin/cog #{command}"
				rescue
					warn "No output from cog command"
				end
			# end
		end
	end
end


# --------------------------------------------------------------------------
#  Status Tasks
# --------------------------------------------------------------------------
#
# These tasks handle bringing the holding page up and down and getting the
# current status.
#

task :status do
	puts ""

	on roles :all do
		info Term::ANSIColor.bold(Term::ANSIColor.yellow("Symlink Status"))
		info "Live Target:    " + getSymlinkTarget(fetch(:live_path))
		info "Staging Target: " + getSymlinkTarget(fetch(:current_path))
	end

	puts ""

	invoke "holding:status"

	puts ""

	invoke "git:status"

	puts ""

	invoke "cog:status"

	puts ""

	invoke "cog:application_status"
end

namespace :holding do
	task :up do
		on roles :all do
			info "Putting holding page up"

			createSymlink("#{fetch(:live_path)}/current", "#{fetch(:live_path)}/holding")
		end

		invoke "holding:status"
	end

	task :down do
		on roles :all do
			info "Taking holding page down"

			createSymlink("#{fetch(:live_path)}/current", "#{fetch(:live_path)}/public")
		end
		invoke "holding:status"
	end

	task :status do
		on roles :all do
			info Term::ANSIColor.bold(Term::ANSIColor.yellow("Holding Page Status"))

			if getSymlinkTarget("#{fetch(:live_path)}/current") == "#{fetch(:live_path)}/holding"
				warn "Holding page is " + Term::ANSIColor.bold(Term::ANSIColor.red("up"))
			else
				info "Holding page is " + Term::ANSIColor.bold(Term::ANSIColor.green("down"))
			end
		end
	end
end


# --------------------------------------------------------------------------
#  Cog Tasks
# --------------------------------------------------------------------------
#
# Tasks to call cog commands.
#

namespace :cog do
	task :status do
		on roles :all do
			info Term::ANSIColor.bold(Term::ANSIColor.yellow("Cog Status"))
			begin
				within fetch(:live_path) do
					execute "bin/cog"
					info "bin/cog ran " + Term::ANSIColor.bold(Term::ANSIColor.green("successfully"))
				end
			rescue
				error "bin/cog failed"
			end
		end
	end

	task :application_status do
		on roles :all do
			info Term::ANSIColor.bold(Term::ANSIColor.yellow("Application Status"))
			begin
				within fetch(:live_path) do
					output = capture "bin/cog", "status"
					output.split("\n").each do |line|
						info "|- " + line
					end
				end
			rescue
				error "bin/cog status " + Term::ANSIColor.bold(Term::ANSIColor.red("failed"))
			end
		end
	end
end


# --------------------------------------------------------------------------
#  Composer Tasks
# --------------------------------------------------------------------------
#
# Tasks to call composer commands.
#

namespace :composer do
	task :check_installed do
		step "Checking Composer" do
			begin
				run "cd #{deploy_to} && php /var/www/vhosts/composer.phar"
			rescue
				raise "Composer may not be installed"
			end
		end
	end

	task :install do
		on roles :all do
			within current_path do
				begin
					execute :php, "/var/www/vhosts/composer.phar", "install", "--optimize-autoloader", "--prefer-dist"
				rescue
					error "Composer failed to run"
				end
			end
		end


		# step "Composer Install" do
		# 	packages = 0

		# 	transaction do
		# 		run "cd #{release_path} && php ~/composer.phar install --optimize-autoloader --prefer-dist" do |channel, stream, data|
		# 			if data.include? "Installing" or data.include? "Updating" # Only update the count for installing and updating output lines
		# 				packages += 1
		# 				if packages < 10 # Remove the previous number and spinner character
		# 					print "\b\b"
		# 				else
		# 					print "\b\b\b"
		# 				end
		# 				print "#{packages} " # Output the new package count
		# 			else
		# 				# puts data
		# 			end
		# 		end
		# 	end
		# end
	end
end


# --------------------------------------------------------------------------
#  Git Tasks
# --------------------------------------------------------------------------
#
# Tasks to run git commands.
#

namespace :git do
	task :status do
		on roles :all do
			info Term::ANSIColor.bold(Term::ANSIColor.yellow("Git Status"))
			info Term::ANSIColor.bold(Term::ANSIColor.blue("This is the commit that is the HEAD for staging, and will not match your live environment unless it has been fully completed."))

			within deploy_to do
				output = capture :cat, "repo/packed-refs", "|", :grep, "'#{master}'"
				output.split("\n").each do |line|
					info "|- " + line
				end
			end
		end
	end

	task :tag_release do
		on roles :all do
			info "Tagging deployment"

			# Fetch the live release folder name (i.e. the timestamp)
			folder = getSymlinkTarget(fetch(:live_path))
			folder = folder.split('/')
			release = folder[folder.length-1]

			tag = "deploy/" + fetch(:branch) + "/" + release

			# Run the tag command locally using `system`
			command = "git checkout " + fetch(:branch) + " && git tag -a " + tag + " -m 'Deployed to " + folder.join('/') + "'"
			system command

			system "git push origin " + tag
		end
	end
end


# --------------------------------------------------------------------------
#  PHPUnit Tasks
# --------------------------------------------------------------------------
#
# Tasks to run unit tests.
#

namespace :phpunit do
	task :run do
		# start_to "PHPUnit Run Tests"
		# run "cd #{release_path} && phpunit"
		# success
	end
end


# --------------------------------------------------------------------------
#  Filesystem Tasks
# --------------------------------------------------------------------------
#
# Tasks to access / modify the filesystem.
#

namespace :filesystem do
	task :create_data_folders do
		folder = "shared_data"

		on roles :all do
			info "Creating data folders"

			within deploy_to do
				execute :mkdir, "-pm", "755", "#{folder}"
				execute :mkdir, "-pm", "755", "#{folder}/#{fetch(:stage)}"
				execute :mkdir, "-pm", "755", "#{folder}/#{fetch(:stage)}/data"
				execute :mkdir, "-pm", "755", "#{folder}/#{fetch(:stage)}/logs"
				execute :mkdir, "-pm", "755", "#{folder}/#{fetch(:stage)}/public"
				execute :mkdir, "-pm", "755", "#{folder}/#{fetch(:stage)}/public/files"
				execute :mkdir, "-pm", "755", "#{folder}/#{fetch(:stage)}/public/resize"
			end

			info "Symlinking data folders"

			target = getSymlinkTarget(current_path)

			createSymlink("#{target}/data",          "#{deploy_to}/#{folder}/#{fetch(:stage)}/data")
			createSymlink("#{target}/logs",          "#{deploy_to}/#{folder}/#{fetch(:stage)}/logs")
			createSymlink("#{target}/public/files",  "#{deploy_to}/#{folder}/#{fetch(:stage)}/public/files")
			createSymlink("#{target}/public/resize", "#{deploy_to}/#{folder}/#{fetch(:stage)}/public/resize")
		end
	end

	task :fix_permissions do
		on roles :all do
			info "Fixing folder permissions"

			target = getSymlinkTarget(current_path)

			fetch(:permissions).each do |directory, permission|
				execute :chmod, "-R", permission, "#{target}/#{directory}"
			end

			# Loop additional commands
			# run "cd #{release_path} && bin/cog deploy:permissions" do |channel, stream, data|
			# 	if stream == :out and data.start_with?("chmod")
			# 		start_to "#{data}"
			# 		run "#{data}"
			# 		success
			# 	else
			# 		# puts data # Print out anything that doesn't match the expected pattern in case of errors
			# 	end
			# end
		end
	end

	namespace :cache do
		task :clear do
			on roles :all do
				info "Clearing cache"
				within release_path do
					execute "ls *|grep -v .gitignore|xargs rm -rf"
				end
			end
		end

		task :warm do
			on roles :all do
				info "Warming cache"
			end
		end
	end
end

def copyDataFiles(fromEnv, toEnv)
	run "cp #{deploy_to}/shared_data/#{fromEnv} #{deploy_to}/shared_data/#{toEnv}"
end


# --------------------------------------------------------------------------
#  Helpers
# --------------------------------------------------------------------------
#
# Helper functions.
#

def confirmRetrieve(var, question)
	value = false

	if fetch("#{var}")
		value = true
	else
		if confirm "#{question}"
			value = true
		else
			value = false
		end
	end

	return value
end

def getSymlinkTarget(symlink)
	target = ""

	if test :readlink, "-v", symlink
		target = capture :readlink, symlink
	end

	return target
end

def createSymlink(symlink, target)
	if getSymlinkTarget(symlink) == target
		info "Symlink already points to target"
		info "-- Symlink: #{symlink}"
		info "-- Target:  #{target}"
	else
		# begin
			execute :rm, "-rf", symlink
			execute :ln, "-s", target, symlink
		# rescue
		# end
	end
end


# --------------------------------------------------------------------------
#  Logging
# --------------------------------------------------------------------------
#
# Additional logging for task start and success events.
#

before "deploy", :log_before_deploy do
	on roles :all do
		info "[" + Term::ANSIColor.bold(Term::ANSIColor.blue(Time.now.strftime("%H:%M:%S"))) + "] Deploying to #{fetch(:stage)}"
	end
end

after "deploy", :log_after_deploy do
	on roles :all do
		info "[" + Term::ANSIColor.bold(Term::ANSIColor.blue(Time.now.strftime("%H:%M:%S"))) + "] Deployed"
	end
end

before "deploy:check", :log_before_check do
	on roles :all do
		info "[" + Term::ANSIColor.bold(Term::ANSIColor.blue(Time.now.strftime("%H:%M:%S"))) + "] Checking"
	end
end

before "deploy:finishing", :log_before_finishing do
	on roles :all do
		info "[" + Term::ANSIColor.bold(Term::ANSIColor.blue(Time.now.strftime("%H:%M:%S"))) + "] Finishing"
	end
end

before "deploy:publishing", :log_before_publishing do
	on roles :all do
		info "[" + Term::ANSIColor.bold(Term::ANSIColor.blue(Time.now.strftime("%H:%M:%S"))) + "] Publishing"
	end
end

after "deploy:publishing", :log_after_publishing do
	on roles :all do
		info "[" + Term::ANSIColor.bold(Term::ANSIColor.blue(Time.now.strftime("%H:%M:%S"))) + "] Published"
	end
end

before "deploy:starting", :log_before_starting do
	on roles :all do
		info "[" + Term::ANSIColor.bold(Term::ANSIColor.blue(Time.now.strftime("%H:%M:%S"))) + "] Starting"
	end
end

before "deploy:updating", :log_before_updating do
	on roles :all do
		info "[" + Term::ANSIColor.bold(Term::ANSIColor.blue(Time.now.strftime("%H:%M:%S"))) + "] Updating"
	end
end