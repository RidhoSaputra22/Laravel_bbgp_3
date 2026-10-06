package main

import (
	"runtime/debug"
)

const syncEngineSchemaVersion = "sync-engine-v1"

// These values can be overridden with -ldflags during a release build.
var (
	engineVersion   = "dev"
	engineHash      = "unknown"
	engineBuildTime = "unknown"
)

type EngineBuildInfo struct {
	Version   string
	Hash      string
	BuildTime string
}

var currentEngineBuild = detectEngineBuild()

func detectEngineBuild() EngineBuildInfo {
	build := EngineBuildInfo{
		Version:   engineVersion,
		Hash:      engineHash,
		BuildTime: engineBuildTime,
	}

	if info, ok := debug.ReadBuildInfo(); ok {
		settings := map[string]string{}
		for _, setting := range info.Settings {
			settings[setting.Key] = setting.Value
		}
		if build.Hash == "unknown" && settings["vcs.revision"] != "" {
			build.Hash = settings["vcs.revision"]
		}
		if build.BuildTime == "unknown" && settings["vcs.time"] != "" {
			build.BuildTime = settings["vcs.time"]
		}
		if build.Version == "dev" && build.Hash != "unknown" {
			build.Version = "git-" + shortHash(build.Hash)
		}
	}

	return build
}

func shortHash(hash string) string {
	if len(hash) > 12 {
		return hash[:12]
	}
	return hash
}

func engineMetadata() map[string]any {
	return map[string]any{
		"schema_version": syncEngineSchemaVersion,
		"name":           "bbpg-sync-worker",
		"version":        currentEngineBuild.Version,
		"hash":           currentEngineBuild.Hash,
		"build_time":     currentEngineBuild.BuildTime,
	}
}

func withEngineMetadata(syncState map[string]any) map[string]any {
	metadata := engineMetadata()
	for key, value := range metadata {
		syncState["engine_"+key] = value
	}
	return syncState
}

func engineVersionLine() string {
	return "version=" + currentEngineBuild.Version +
		" hash=" + currentEngineBuild.Hash +
		" build_time=" + currentEngineBuild.BuildTime
}
